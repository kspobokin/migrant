<?php
namespace App\Http\Controllers\Counterparty;

use App\Http\Controllers\Controller;
use App\Models\Document;
use App\Services\Transliterator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfileController extends Controller
{
    public function show()
    {
        $contractor = Auth::guard('counterparty')->user();
        $documents = Document::where('contractor1_id', $contractor->id)
                            ->orWhere('contractor2_id', $contractor->id)->get();
        return view('counterparty.profile', compact('contractor', 'documents'));
    }

    public function update(Request $request)
    {
        $contractor = Auth::guard('counterparty')->user();
        $data = $request->validate([
            'last_name_ru' => 'required|string|max:255',
            'first_name_ru' => 'required|string|max:255',
            'patronymic_ru' => 'required|string|max:255',
            'email' => 'required_without:phone|email|unique:contractors,email,' . $contractor->id,
            'phone' => 'required_without:email|string|max:20|unique:contractors,phone,' . $contractor->id,
            'inn' => 'nullable|string|regex:/^\d{10}$|regex:/^\d{12}$/',
            'insurance_policy' => 'nullable|string|max:50',
            'registration_address' => 'nullable|string|max:255',
            'extra_fields' => 'nullable|json'
        ]);

        $data['last_name_lat'] = Transliterator::transliterate($data['last_name_ru']);
        $data['first_name_lat'] = Transliterator::transliterate($data['first_name_ru']);
        $data['patronymic_lat'] = Transliterator::transliterate($data['patronymic_ru']);

        $contractor->update($data);
        return redirect()->route('counterparty.profile')->with('success', 'Profile updated successfully');
    }
}
