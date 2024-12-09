<?php

namespace App\Http\Controllers;

use App\Events\CsvUploadEvent;
use App\Http\Requests\CsvImportRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CsvImportController extends Controller
{
    public function __invoke(CsvImportRequest $request)
    {
        try {
            if ($request->ajax()) {
                $fileName = basename($request->file('file')->store('/public/csv-files'));
                CsvUploadEvent::dispatch(base_path() . "/public/storage/csv-files/$fileName",$request->name,TRUE);
                return response()->json(['message'=>"completed"]);
            } else {
                return view('upload-page');
            }
        } catch (\Throwable $th) {
            return response()->json(['message' => $th->getMessage()]);
        }
    }
}
