<?php

namespace App\Http\Controllers\Api\Mentor;

use App\Http\Controllers\Controller;
use App\Models\Mentor\ProfileSetting;
use App\Traits\HttpResponses;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileSettingController extends Controller
{
 use HttpResponses;
    public function addExperience(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'employment_type' => 'required|string|max:255',
                'company' => 'required|string|max:255',
                'location' => 'nullable|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'description' => 'nullable|string',
                'is_current' => 'boolean',
            ]);

            $profileSetting = ProfileSetting::firstOrCreate(
                ['user_id' => Auth::id()],
                ['about_me' => null, 'experience' => [], 'education' => []]
            );

            $experience = $profileSetting->experience ?? [];
            $experience[] = $validated;
            $profileSetting->experience = $experience;
            $profileSetting->save();

            return $this->success($profileSetting, 'Experience added successfully.');

        } catch (\Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'Failed to add experience.');
        }
    }
    public function updateExperience(Request $request, $index)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'employment_type' => 'sometimes|required|string|max:255',
                'company' => 'sometimes|required|string|max:255',
                'location' => 'sometimes|nullable|string|max:255',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|nullable|date|after:start_date',
                'description' => 'sometimes|nullable|string',
                'is_current' => 'sometimes|boolean',
            ]);

            $profileSetting = ProfileSetting::where('user_id', Auth::id())->firstOrFail();
            $experience = $profileSetting->experience ?? [];

            if (isset($experience[$index])) {
                // Update the specific experience entry
                $experience[$index] = array_merge($experience[$index], $validated); // Merge validated data
                $profileSetting->experience = array_values($experience); // Reindex the array
                $profileSetting->save();
            } else {
                return $this->error(null, 'Experience entry not found.', 404);
            }

            return $this->success($profileSetting, 'Experience updated successfully.');

        } catch (\Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'Failed to update experience.');
        }
    }


    public function removeExperience($index)
    {
        try {
            $profileSetting = ProfileSetting::where('user_id', Auth::id())->firstOrFail();

            $experience = $profileSetting->experience ?? [];
            if (isset($experience[$index])) {
                unset($experience[$index]);
                $profileSetting->experience = array_values($experience); // Reindex the array
                $profileSetting->save();
            } else {
                return $this->error(null, 'Experience entry not found.', 404);
            }

            return $this->success($profileSetting, 'Experience removed successfully.');

        } catch (\Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'Failed to remove experience.');
        }
    }

    public function addEducation(Request $request)
    {
        try {
            $validated = $request->validate([
                'school' => 'required|string|max:255',
                'degree' => 'required|string|max:255',
                'field_of_study' => 'required|string|max:255',
                'start_date' => 'required|date',
                'end_date' => 'nullable|date|after:start_date',
                'grade' => 'nullable|string|max:255',
                'description' => 'nullable|string',
            ]);

            $profileSetting = ProfileSetting::firstOrCreate(
                ['user_id' => Auth::id()],
                ['about_me' => null, 'experience' => [], 'education' => []]
            );

            $education = $profileSetting->education ?? [];
            $education[] = $validated;
            $profileSetting->education = $education;
            $profileSetting->save();

            return $this->success($profileSetting, 'Education added successfully.');

        } catch (\Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'Failed to add education.');
        }
    }

    public function updateEducation(Request $request, $index)
    {
        try {
            $validated = $request->validate([
                'school' => 'sometimes|required|string|max:255',
                'degree' => 'sometimes|required|string|max:255',
                'field_of_study' => 'sometimes|required|string|max:255',
                'start_date' => 'sometimes|required|date',
                'end_date' => 'sometimes|nullable|date|after:start_date',
                'grade' => 'sometimes|nullable|string|max:255',
                'description' => 'sometimes|nullable|string',
            ]);

            $profileSetting = ProfileSetting::where('user_id', Auth::id())->firstOrFail();
            $education = $profileSetting->education ?? [];

            if (isset($education[$index])) {
                // Update the specific education entry
                $education[$index] = array_merge($education[$index], $validated);
                $profileSetting->education = array_values($education);
                $profileSetting->save();
            } else {
                return $this->error(null, 'Education entry not found.', 404);
            }

            return $this->success($profileSetting, 'Education updated successfully.');

        } catch (\Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'Failed to update education.');
        }
    }


    public function removeEducation($index)
    {
        try {
            $profileSetting = ProfileSetting::where('user_id', Auth::id())->firstOrFail();

            $education = $profileSetting->education ?? [];
            if (isset($education[$index])) {
                unset($education[$index]);
                $profileSetting->education = array_values($education); // Reindex the array
                $profileSetting->save();
            } else {
                return $this->error(null, 'Education entry not found.', 404);
            }

            return $this->success($profileSetting, 'Education removed successfully.');

        } catch (\Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'Failed to remove education.');
        }
    }

    public function updateAboutMe(Request $request)
    {
        try {
            $validated = $request->validate([
                'about_me' => 'required|string',
            ]);

            $profileSetting = ProfileSetting::firstOrCreate(
                ['user_id' => Auth::id()],
                ['experience' => [], 'education' => []]
            );

            $profileSetting->about_me = $validated['about_me'];
            $profileSetting->save();

            return $this->success($profileSetting, 'About Me updated successfully.');

        } catch (\Exception $e) {
            return $this->error(['error' => $e->getMessage()], 'Failed to update "About Me".');
        }
    }
}
