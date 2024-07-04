<?php

namespace DevForest\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;

class UpdateGeneratedReport extends FormRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'query' => ['required', function ($attribute, $value, $fail) {
                $dangerousQueries = ['create', 'alter', 'truncate', 'drop', 'insert', 'update', 'delete'];
                foreach ($dangerousQueries as $dangerousQuery) {
                    if (preg_match("/\b($dangerousQuery)\b/i", strtolower($value))) {
                        return $fail("Unable to execute `{$dangerousQuery}` query");
                    }
                }
                // Check if the query is executable
                try {
                    DB::beginTransaction();
                    DB::select(DB::raw($value . ' limit 1')->getValue(DB::getQueryGrammar()));
                    DB::rollBack();
                } catch (\Exception $e) {
                    return $fail("The query is not executable: " . $e->getMessage());
                }
            }],
            'name' => 'required',
        ];
    }
}
