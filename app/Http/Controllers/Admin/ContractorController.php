<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Contractor;
use App\Services\Transliterator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ContractorController extends Controller
{
    public function index()
    {
        return view('admin.contractors.index', ['contractors' => Contractor::all()]);
    }

    public function create()
    {
        return view('admin.contractors.create');
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'last_name_ru' => 'required|string|max:255',
            'first_name_ru' => 'required|string|max:255',
            'patronymic_ru' => 'required|string|max:255',
            'email' => 'required_without:phone|email|unique:contractors,email',
            'phone' => 'required_without:email|string|max:20|unique:contractors,phone',
            'inn' => 'nullable|string|regex:/^\d{10}$|regex:/^\d{12}$/',
            'insurance_policy' => 'nullable|string|max:50',
            'registration_address' => 'nullable|string|max:255',
            'type' => 'required|in:individual,legal',
            'role' => 'required|in:customer,performer',
            'extra_fields' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        if ($data['extra_fields'] && !json_decode($data['extra_fields'])) {
            return back()->withErrors(['extra_fields' => 'Invalid JSON format'])->withInput();
        }

        $data['last_name_lat'] = Transliterator::transliterate($data['last_name_ru']);
        $data['first_name_lat'] = Transliterator::transliterate($data['first_name_ru']);
        $data['patronymic_lat'] = Transliterator::transliterate($data['patronymic_ru']);

        Contractor::create($data);
        return redirect()->route('contractors.index')->with('success', 'Contractor created successfully');
    }

    public function show($id)
    {
        return view('admin.contractors.show', ['contractor' => Contractor::findOrFail($id)]);
    }

    public function edit($id)
    {
        return view('admin.contractors.edit', ['contractor' => Contractor::findOrFail($id)]);
    }

    public function update(Request $request, $id)
    {
        $contractor = Contractor::findOrFail($id);
        $validator = Validator::make($request->all(), [
            'last_name_ru' => 'required|string|max:255',
            'first_name_ru' => 'required|string|max:255',
            'patronymic_ru' => 'required|string|max:255',
            'email' => 'required_without:phone|email|unique:contractors,email,' . $id,
            'phone' => 'required_without:email|string|max:20|unique:contractors,phone,' . $id,
            'inn' => 'nullable|string|regex:/^\d{10}$|regex:/^\d{12}$/',
            'insurance_policy' => 'nullable|string|max:50',
            'registration_address' => 'nullable|string|max:255',
            'type' => 'required|in:individual,legal',
            'role' => 'required|in:customer,performer',
            'extra_fields' => 'nullable|json',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $data = $validator->validated();

        if ($data['extra_fields'] && !json_decode($data['extra_fields'])) {
            return back()->withErrors(['extra_fields' => 'Invalid JSON format'])->withInput();
        }

        $data['last_name_lat'] = Transliterator::transliterate($data['last_name_ru']);
        $data['first_name_lat'] = Transliterator::transliterate($data['first_name_ru']);
        $data['patronymic_lat'] = Transliterator::transliterate($data['patronymic_ru']);

        $contractor->update($data);
        return redirect()->route('contractors.index')->with('success', 'Contractor updated successfully');
    }

    public function destroy($id)
    {
        Contractor::findOrFail($id)->delete();
        return redirect()->route('contractors.index')->with('success', 'Contractor deleted successfully');
    }
}
