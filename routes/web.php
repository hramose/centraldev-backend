<?php
Route::get('/', function () {
    return redirect('/api');
});
Route::get('/mail', function() {
    return view('emails.verify-email')->with('verify_url', 'blabla');
});
