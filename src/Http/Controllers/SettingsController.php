<?php

namespace Xenon007\SeatSpfarm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Xenon007\SeatSpfarm\Models\SpfarmCharacter;
use Xenon007\SeatSpfarm\Models\SpfarmUserSetting;

/**
 * Class SettingsController
 *
 * Presents a form for users to configure which of their characters are
 * considered part of the skillpoint farm and to toggle other plugin
 * preferences. Persists submitted data to the plugin's own tables.
 */
class SettingsController extends Controller
{
    /**
     * Display the settings form.
     *
     * @return \Illuminate\Contracts\View\View
     */
    public function index()
    {
        $user = auth()->user();

        // Retrieve the user's characters via the SeAT web relationship. If the
        // relationship is missing, fall back to an empty collection.
        $characters = $user->characters ?? collect();

        // Load existing per‑character settings keyed by character_id for easy
        // lookup in the view.
        $entries = SpfarmCharacter::where('user_id', $user->id)
            ->get()
            ->keyBy('character_id');

        // Retrieve or create the per‑user settings row.
        $userSetting = SpfarmUserSetting::firstOrCreate([
            'user_id' => $user->id,
        ]);

        return view('seat-spfarm::settings', [
            'characters'  => $characters,
            'entries'     => $entries,
            'userSetting' => $userSetting,
        ]);
    }

    /**
     * Persist submitted settings.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $user = auth()->user();

        // Save global setting: whether to show the idle monitor table.
        $showIdle = $request->has('show_idle_table');
        SpfarmUserSetting::updateOrCreate([
            'user_id' => $user->id,
        ], [
            'show_idle_table' => $showIdle,
        ]);

        // The characters input will look like:
        // characters[<character_id>][is_farm] = on
        // characters[<character_id>][pi_enabled] = on
        // characters[<character_id>][plan_text] = '...' (optional)
        $characters = (array) $request->input('characters', []);

        // Keep track of character IDs seen for later cleanup
        $seenCharacterIds = [];

        foreach ($characters as $characterId => $fields) {
            $seenCharacterIds[] = (int) $characterId;

            $isFarm = isset($fields['is_farm']);
            $piEnabled = isset($fields['pi_enabled']);
            $planText = $fields['plan_text'] ?? null;

            SpfarmCharacter::updateOrCreate([
                'user_id'     => $user->id,
                'character_id' => (int) $characterId,
            ], [
                'is_farm'    => $isFarm,
                'pi_enabled' => $piEnabled,
                'plan_text'  => $planText,
            ]);
        }

        // Optionally clean up entries for characters not present in the form
        // submission. This removes stale rows when a character is removed
        // entirely from the farm roster. We only do this for the current user.
        SpfarmCharacter::where('user_id', $user->id)
            ->whereNotIn('character_id', $seenCharacterIds)
            ->delete();

        return redirect()->route('seat-spfarm.settings')->with('status', __('Settings saved successfully.'));
    }
}