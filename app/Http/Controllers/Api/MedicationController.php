<?php
namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Http\Resources\MedicationResource; // Import the resource
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule; // Needed for unique rule on update
use Illuminate\Http\JsonResponse; // For type hinting

class MedicationController extends Controller
{
    /**
     * Display a listing of the medications.
     * Typically used for selection lists when creating prescriptions.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index(Request $request)
    {
        // Allow simple searching by name
        $query = Medication::query();

        if ($request->has('search')) {
            $searchTerm = $request->input('search');
            $query->where('name', 'like', "%{$searchTerm}%");
        }

        // Order alphabetically by name
        $medications = $query->orderBy('name')->paginate(25); // Paginate results

        return MedicationResource::collection($medications);
    }

    /**
     * Store a newly created medication in storage.
     * Assumes authenticated user has permission (e.g., admin or doctor).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \App\Http\Resources\MedicationResource|\Illuminate\Http\JsonResponse
     */
    public function store(Request $request): MedicationResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:medications,name', // Ensure name is unique
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422); // Unprocessable Entity
        }

        try {
            $medication = Medication::create($validator->validated());

            return (new MedicationResource($medication))
                    ->response()
                    ->setStatusCode(201); // Created

        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Failed to create medication.'], 500);
        }
    }

    /**
     * Display the specified medication.
     *
     * @param  \App\Models\Medication  $medication (Route Model Binding)
     * @return \App\Http\Resources\MedicationResource
     */
    public function show(Medication $medication): MedicationResource
    {
        // Route model binding automatically finds the medication by ID or throws 404
        return new MedicationResource($medication);
    }

    /**
     * Update the specified medication in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Medication  $medication (Route Model Binding)
     * @return \App\Http\Resources\MedicationResource|\Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Medication $medication): MedicationResource|JsonResponse
    {
        $validator = Validator::make($request->all(), [
            // Ensure name is unique, but ignore the current medication's ID
            'name' => [
                'sometimes', // Validate only if present
                'required',
                'string',
                'max:255',
                Rule::unique('medications')->ignore($medication->id),
            ],
            'description' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        try {
            $medication->update($validator->validated());

            // Return the updated resource
            return new MedicationResource($medication);

        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Failed to update medication.'], 500);
        }
    }

    /**
     * Remove the specified medication from storage.
     * **Caution:** Consider preventing deletion if medication is used in prescriptions.
     *
     * @param  \App\Models\Medication  $medication (Route Model Binding)
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(Medication $medication): JsonResponse
    {
        // **IMPORTANT CHECK:** Prevent deleting if already prescribed
        // Ensure the 'prescriptions' relationship exists on the Medication model
        if ($medication->prescriptions()->exists()) {
            return response()->json([
                'message' => 'Cannot delete medication because it has existing prescriptions associated with it.'
            ], 409); // 409 Conflict is appropriate here
        }

        try {
            $medication->delete();
            return response()->json(null, 204); // No Content

        } catch (\Exception $e) {
            report($e);
            return response()->json(['message' => 'Failed to delete medication.'], 500);
        }
    }
}