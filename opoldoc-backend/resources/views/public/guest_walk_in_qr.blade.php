@extends('layouts.app')

@section('title', 'Guest Walk-in QR')

@section('body')
    @php
        $displayUrl = $activeUrl ?: $staticUrl;
        $qrData = urlencode($displayUrl);
        $qrImg = 'https://api.qrserver.com/v1/create-qr-code/?size=320x320&data='.$qrData;
    @endphp

    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-xl bg-white border border-slate-200 rounded-[18px] shadow-[0_2px_10px_rgba(15,23,42,0.04)] overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl overflow-hidden border border-slate-200 bg-white flex items-center justify-center">
                        <img src="{{ asset('images/opoldoc3.png') }}" alt="Opol Doctors Medical Clinic" class="w-full h-full object-cover">
                    </div>
                    <div class="min-w-0">
                        <div class="font-serif font-bold text-slate-900 leading-tight">Opol Doctors Medical Clinic</div>
                        <div class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Guest Walk-in QR</div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="flex flex-col items-center">
                    <div class="w-[320px] h-[320px] rounded-2xl border border-slate-200 bg-white overflow-hidden flex items-center justify-center">
                        <img src="{{ $qrImg }}" alt="Guest walk-in QR" class="w-[320px] h-[320px] object-contain">
                    </div>
                    <div class="mt-4 w-full text-xs text-slate-600 break-words">
                        <div class="text-[0.68rem] text-slate-400 uppercase tracking-widest mb-1">Current link</div>
                        <div class="rounded-xl border border-slate-200 bg-slate-50 px-3 py-2">
                            <a href="{{ $displayUrl }}" class="text-cyan-700 font-semibold hover:underline">{{ $displayUrl }}</a>
                        </div>
                        <!-- <div class="mt-3 text-[0.68rem] text-slate-400 uppercase tracking-widest mb-1">Static link (for printed signs)</div> -->
                        <!-- <div class="rounded-xl border border-slate-200 bg-white px-3 py-2">
                            <a href="{{ $staticUrl }}" class="text-cyan-700 font-semibold hover:underline">{{ $staticUrl }}</a>
                        </div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

