<?php
Route::get('/mail', function() {
    return view('emails.verify-email')->with('verify_url', 'blabla');
});
