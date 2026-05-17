<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AttendanceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'attend_start' => 'required|date_format:H:i|before_or_equal:attend_end',
            'attend_end' => 'required|date_format:H:i|after_or_equal:attend_start',
            'rest_start' => 'nullable|date_format:H:i|after_or_equal:attend_start|before_or_equal:attend_end',
            'rest_end' => 'nullable|date_format:H:i|after_or_equal:rest_start|before_or_equal:attend_end',
            'rests.*.rest_start' => 'nullable|date_format:H:i|after_or_equal:attend_start|before_or_equal:attend_end',
            'rests.*.rest_end' => 'nullable|date_format:H:i|after_or_equal:rests.*.rest_start|before_or_equal:attend_end',
            'comment' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'attend_start.required' => '出勤時間を入力してください',
            'attend_start.date_format' => '出勤時間をHH:mm形式で入力してください',
            'attend_start.before_or_equal' => '出勤時間が不適切な値です',
            'attend_end.required' => '退勤時間を入力してください',
            'attend_end.date_format' => '退勤時間をHH:mm形式で入力してください',
            'attend_end.after_or_equal' => '退勤時間が不適切な値です',
            'rest_start.date_format' => '休憩開始時間をHH:mm形式で入力してください',
            'rest_start.after_or_equal' => '休憩時間が不適切な値です',
            'rest_start.before_or_equal' => '休憩時間が不適切な値です',
            'rest_end.date_format' => '休憩終了時間をHH:mm形式で入力してください',
            'rest_end.after_or_equal' => '休憩時間が不適切な値です',
            'rest_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.rest_start.date_format' => '休憩開始時間をHH:mm形式で入力してください',
            'rests.*.rest_start.after_or_equal' => '休憩時間が不適切な値です',
            'rests.*.rest_start.before_or_equal' => '休憩時間が不適切な値です',
            'rests.*.rest_end.date_format' => '休憩終了時間をHH:mm形式で入力してください',
            'rests.*.rest_end.after_or_equal' => '休憩時間が不適切な値です',
            'rests.*.rest_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'comment.required' => '備考を記入してください',
            'comment.string' => '備考は文字列で入力してください',
            'comment.max' => '備考は255文字以内で入力してください',
        ];
    }
}
