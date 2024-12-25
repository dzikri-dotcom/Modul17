<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Support\Facades\Storage;
use RealRashid\SweetAlert\Facades\Alert;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\EmployeesExport;
use PDF;



class EmployeeController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pageTitle = 'Employee List';
        confirmDelete();
        $positions = Position::all();

        return view('employee.index', [
            'pageTitle' => $pageTitle,
            'positions' => $positions
        ]);

    }





    public function create()
    {
        $pageTitle = 'Create Employee';

        // ELOQUENT
        $positions = Position::all();

        return view('employee.create', compact('pageTitle', 'positions'));
    }

    public function store(Request $request)
    {
        $messages = [
            'required' => ':Attribute harus diisi.',
            'email' => 'Isi :attribute dengan format yang benar',
            'numeric' => 'Isi :attribute dengan angka'
        ];

        $validator = Validator::make($request->all(), [
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
        ], $messages);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        // Get File
        $file = $request->file('cv');

        if ($file != null) {
            $originalFilename = $file->getClientOriginalName();
            $encryptedFilename = $file->hashName();

            // Store File
            $file->store('public/files');
        }

        // ELOQUENT
        $employee = new Employee;
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;

        if ($file != null) {
            $employee->original_filename = $originalFilename;
            $employee->encrypted_filename = $encryptedFilename;
        }
        $employee->save();
        Alert::success('Added Successfully', 'Employee Data Added Successfully.');
        return redirect()->route('employees.index');
    }



    public function show(string $id)
    {
        $pageTitle = 'Employee Detail';

        // ELOQUENT
        $employee = Employee::find($id);

        return view('employee.show', compact('pageTitle', 'employee'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $pageTitle = 'Edit Employee';

        // ELOQUENT
        $positions = Position::all();
        $employee = Employee::find($id);

        return view('employee.edit', compact('pageTitle', 'positions', 'employee'));
    }


    public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'firstName' => 'required',
            'lastName' => 'required',
            'email' => 'required|email',
            'age' => 'required|numeric',
            'cv' => 'nullable|file|mimes:pdf|max:2048', // CV tidak wajib, hanya file PDF maksimal 2MB
        ]);

        // Cari employee di database
        $employee = Employee::findOrFail($id);

        // Update data dasar
        $employee->firstname = $request->firstName;
        $employee->lastname = $request->lastName;
        $employee->email = $request->email;
        $employee->age = $request->age;
        $employee->position_id = $request->position;



        // Jika ada file CV yang diunggah
        if ($request->hasFile('cv')) {
            // Hapus file lama jika ada
            if ($employee->encrypted_filename) {
                Storage::disk('public')->delete('files/' . $employee->encrypted_filename);
            }

            // Simpan file baru
            $file = $request->file('cv');
            $encryptedFilename = $file->hashName();
            $file->storeAs('files', $encryptedFilename, 'public');

            // Simpan informasi file baru ke database
            $employee->original_filename = $file->getClientOriginalName();
            $employee->encrypted_filename = $encryptedFilename;
        }

        // Simpan perubahan
        $employee->save();

        Alert::success('Changed Successfully', 'Employee Data Changed Successfully.');
        // Redirect ke halaman index atau detail
        return redirect()->route('employees.index')->with('success', 'Employee updated successfully!');
    }


    public function destroy(string $id)
    {
        // Cari employee berdasarkan ID
        $employee = Employee::findOrFail($id);

        // Hapus file CV jika ada
        if ($employee->encrypted_filename) {
            $filePath = 'files/' . $employee->encrypted_filename;

            // Periksa apakah file CV ada di storage
            if (Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath); // Hapus file CV
            }
        }

        // Hapus data employee dari database
        $employee->delete();
        Alert::success('Deleted Successfully', 'Employee Data Deleted Successfully.');

        // Redirect ke halaman daftar employees dengan pesan sukses
        return redirect()->route('employees.index')->with('success', 'Employee and associated CV deleted successfully!');
    }

    public function downloadFile($employeeId)
    {
        $employee = Employee::find($employeeId);
        $encryptedFilename = 'public/files/' . $employee->encrypted_filename;
        $downloadFilename = Str::lower($employee->firstname . '_' . $employee->lastname . '_cv.pdf');

        if (Storage::exists($encryptedFilename)) {
            return Storage::download($encryptedFilename, $downloadFilename);
        }
    }

    public function getData(Request $request)
    {
        $employees = Employee::with('position');

        if ($request->ajax()) {
            return datatables()->of($employees)
                ->addIndexColumn()
                ->addColumn('actions', function ($employee) {
                    return view('employee.actions', compact('employee'));
                })
                ->toJson();
        }
    }

    public function exportExcel()
    {
        return Excel::download(new EmployeesExport, 'employees.xlsx');
    }

    public function exportPdf()
    {
        $employees = Employee::all();

        $pdf = PDF::loadView('employee.export_pdf', compact('employees'));

        return $pdf->download('employees.pdf');
    }




}
