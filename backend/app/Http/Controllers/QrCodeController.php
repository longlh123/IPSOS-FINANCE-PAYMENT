<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QrCodeController extends Controller
{
    public function generate(Request $request){
        $data = $request->query('data', 'Hello World!');

         $qr = QrCode::format('svg') // dùng SVG, không cần Imagick
                    ->size(300)
                    ->generate($data);

        return response($qr)->header('Content-Type', 'image/svg+xml');
    }
}
