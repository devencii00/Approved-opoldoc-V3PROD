@extends('layouts.app')

@section('title', 'Guest Walk-in')

@section('body')
    <div class="min-h-screen flex items-center justify-center px-4 py-10">
        <div class="w-full max-w-lg bg-white border border-slate-200 rounded-[18px] shadow-[0_2px_10px_rgba(15,23,42,0.04)] overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50">
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-xl overflow-hidden border border-slate-200 bg-white flex items-center justify-center">
                        <img src="{{ asset('images/opoldoc3.png') }}" alt="Opol Doctors Medical Clinic" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="font-serif font-bold text-slate-900 leading-tight">Opol Doctors Medical Clinic</div>
                        <div class="text-[0.7rem] text-slate-400 uppercase tracking-widest">Guest Walk-in</div>
                    </div>
                </div>
            </div>

            <div class="p-6">
                <div class="rounded-2xl border border-amber-200 bg-amber-50 px-4 py-3 text-[0.85rem] text-amber-900">
                    {{ $message ?? 'The clinic is currently closed. Please return at 8:00 AM.' }}
                </div>
                <div class="mt-4 text-xs text-slate-500">
                    If you need the latest QR page, open: <a href="{{ $qrUrl ?? url('/guest-walk-in/qr') }}" class="text-cyan-700 font-semibold hover:underline">{{ $qrUrl ?? url('/guest-walk-in/qr') }}</a>
                </div>
            </div>
        </div>
    </div>
@endsection
