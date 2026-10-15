<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin;

use App\Models\Plan;
use Illuminate\Validation\Rule;

class UpdatePlanRequest extends StorePlanRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $plan = $this->route('plan');
        $planId = $plan instanceof Plan ? $plan->id : $plan;

        return array_merge(parent::rules(), [
            'slug' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('plans', 'slug')->ignore($planId),
            ],
        ]);
    }
}
