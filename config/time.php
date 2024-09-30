<?php
//Atur timezone
date_default_timezone_set(timezoneId: 'Asia/Jakarta');

//Fungsi yang bisa dipanggil
function timestamp($timestamp)
{
    //Mengatur variable
    $target_time = new DateTime(datetime: $timestamp);
    $current_time = new DateTime();

    //Menghitung perbedaan waktu
    $interval = $current_time->diff(targetObject: $target_time);

    //Mengubah perbedaan menjadi total waktu
    $years = $interval->y;
    $months = $interval->m;
    $days = $interval->d;
    $hours = $interval->h;
    $minutes = $interval->i;
    $seconds = $interval->s;

    // if else dan return
    if ($years > 0) {
        return "$years tahun yang lalu";
    } elseif ($months > 0) {
        return "$months bulan yang lalu";
    } elseif ($days > 0) {
        return "$days hari yang lalu";
    } elseif ($hours > 0) {
        return "$hours jam yang lalu";
    } elseif ($minutes > 0) {
        return "$minutes menit yang lalu";
    } elseif ($seconds > 0) {
        return "$seconds detik yang lalu";
    } else {
        return "baru saja";
    }
}