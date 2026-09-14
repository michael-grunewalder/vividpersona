<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CurrentTeamController extends Controller
{
    /**
     * Set the authenticated user's active team.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'team_id' => ['required', 'string'],
        ]);

        $team = $request->user()->teams()->whereKey($request->team_id)->firstOrFail();

        $request->user()->setCurrentTeam($team);

        return redirect()->back();
    }
}
