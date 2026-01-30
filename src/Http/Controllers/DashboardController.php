<?php

namespace Xenon007\SeatSpfarm\Http\Controllers;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Routing\Controller;
use Xenon007\SeatSpfarm\Models\SpfarmCharacter;
use Xenon007\SeatSpfarm\Models\SpfarmUserSetting;

/**
 * Class DashboardController
 *
 * Constructs and returns the SP Farming dashboard. The dashboard consists
 * primarily of two tables: one showing characters that are actively
 * participating in the farm and another optional table showing all other
 * characters along with their training status (idle monitor). Due to the
 * variety of SeAT installations, many of the advanced columns gracefully
 * degrade to 'unknown' or similar when data is unavailable.
 */
class DashboardController extends Controller
{
    /**
     * Display the dashboard.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $user = auth()->user();

        // All characters belonging to the user via SeAT's relationship. If
        // unavailable, fall back to an empty collection.
        $characters = $user->characters ?? collect();

        // Per‑character settings keyed by character_id
        $entries = SpfarmCharacter::where('user_id', $user->id)
            ->get()
            ->keyBy('character_id');

        // Per‑user settings row
        $userSetting = SpfarmUserSetting::firstOrCreate([
            'user_id' => $user->id,
        ]);

        $farmRows = [];
        $idleRows = [];

        foreach ($characters as $character) {
            $charId = (int) ($character->character_id ?? $character->id ?? 0);
            $charName = $character->name ?? $character->character_name ?? __('Unknown');

            // Determine if this character is in the farm
            /** @var \Xenon007\SeatSpfarm\Models\SpfarmCharacter|null $settings */
            $settings = $entries->get($charId);
            $isFarm = $settings && $settings->is_farm;

            // Compile a row structure with all columns. Many values degrade
            // gracefully if data is missing or exceptions occur.
            $row = [
                'id' => $charId,
                'name' => $charName,
                'training_skill' => 'unknown',
                'plan_count' => 0,
                'extraction' => null,
                'pi_status' => $settings ? ($settings->pi_enabled ? 'enabled' : 'disabled') : 'disabled',
                'location' => 'unknown',
                'online' => 'unknown',
                'omega' => 'unknown',
                'is_idle' => true,
                'plan_text' => $settings->plan_text ?? null,
            ];

            // Attempt to derive training information from the skill queue. Not
            // all installations expose a skillQueue relationship. We wrap
            // all accesses in try/catch to avoid fatal errors if unknown.
            try {
                // Seat EVEAPI models often expose a 'skillqueue' relationship
                // returning a collection of queue entries ordered by
                // queue_position. We normalise to a collection.
                $queue = null;
                if (method_exists($character, 'skillqueue')) {
                    $queue = $character->skillqueue;
                } elseif (property_exists($character, 'skillqueue')) {
                    $queue = $character->skillqueue;
                } elseif (method_exists($character, 'skillQueue')) {
                    $queue = $character->skillQueue;
                }

                if ($queue instanceof Collection || is_array($queue)) {
                    $q = collect($queue);
                    $row['plan_count'] = $q->count();
                    if ($q->count() > 0) {
                        // Sort by queue_position ascending for first skill
                        $sorted = $q->sortBy(function ($item) {
                            return $item->queue_position ?? 0;
                        });
                        $first = $sorted->first();
                        $last = $sorted->last();
                        // Current training skill name
                        if ($first) {
                            // Many skill queue models expose skill_name or a
                            // skill relationship with a name.
                            $row['training_skill'] = $first->skill_name
                                ?? ($first->skill->name ?? 'unknown');
                            $row['is_idle'] = false;
                        }
                        // Extraction date is finish_date of last entry
                        if ($last && property_exists($last, 'finish_date')) {
                            // Cast to Carbon for easier formatting later
                            $row['extraction'] = Carbon::parse($last->finish_date);
                        }
                    }
                }
            } catch (\Throwable $e) {
                // Leave training fields at default values
            }

            // Attempt to derive location information. Newer SeAT versions
            // expose a 'location' relationship on CharacterInfo or the
            // character model itself. We attempt common properties.
            try {
                $locName = null;
                if (method_exists($character, 'location')) {
                    $loc = $character->location;
                    $locName = $loc->name ?? null;
                }
                if (! $locName && isset($character->location)) {
                    $loc = $character->location;
                    $locName = $loc->name ?? null;
                }
                if (! $locName && isset($character->location_name)) {
                    $locName = $character->location_name;
                }
                if ($locName) {
                    $row['location'] = $locName;
                }
            } catch (\Throwable $e) {
                // Location remains unknown
            }

            // Attempt to derive last activity. We try properties commonly
            // exposed on CharacterInfo or membership models (last_login,
            // last_logout, last_known_online).
            try {
                $lastTime = null;
                // Some models provide last_login/last_logout timestamps
                if (isset($character->last_login)) {
                    $lastTime = Carbon::parse($character->last_login);
                }
                if (isset($character->last_logout)) {
                    $logout = Carbon::parse($character->last_logout);
                    // Use logout if more recent
                    if (! $lastTime || $logout->greaterThan($lastTime)) {
                        $lastTime = $logout;
                    }
                }
                // last_known_online custom attribute
                if (isset($character->last_known_online)) {
                    $lko = Carbon::parse($character->last_known_online);
                    if (! $lastTime || $lko->greaterThan($lastTime)) {
                        $lastTime = $lko;
                    }
                }
                if ($lastTime) {
                    // Format as relative string (e.g. 2d 4h ago)
                    $diff = $lastTime->diffForHumans(now(), [
                        'parts' => 3,
                        'syntax' => Carbon::DIFF_ABSOLUTE,
                    ]);
                    $row['online'] = $diff . ' ago';
                }
            } catch (\Throwable $e) {
                // Remain unknown
            }

            // Attempt to derive Omega status: heuristically mark as 'omega'
            // when character appears to be training and has plan_count > 0.
            if (! $row['is_idle'] && $row['plan_count'] > 0) {
                $row['omega'] = 'omega?';
            }

            if ($isFarm) {
                $farmRows[] = $row;
            } else {
                $idleRows[] = $row;
            }
        }

        // Sort alphabetically by character name for both tables
        usort($farmRows, fn ($a, $b) => strcmp($a['name'], $b['name']));
        usort($idleRows, fn ($a, $b) => strcmp($a['name'], $b['name']));

        return view('seat-spfarm::dashboard', [
            'farmRows'     => $farmRows,
            'idleRows'     => $idleRows,
            'showIdle'     => (bool) $userSetting->show_idle_table,
        ]);
    }
}