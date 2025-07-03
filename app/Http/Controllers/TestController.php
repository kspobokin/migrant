<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function testSetup()
    {
        return response()->json(['message' => 'Setup test successful']);
    }
}
