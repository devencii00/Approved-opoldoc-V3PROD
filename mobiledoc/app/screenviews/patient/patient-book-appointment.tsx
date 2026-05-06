import React, { useEffect, useMemo, useState } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Pressable,
  ScrollView,
  StatusBar,
  SafeAreaView,
  TextInput,
} from 'react-native';
import { useRouter } from 'expo-router';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  cyan400: '#22d3ee',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate300: '#cbd5e1',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate600: '#475569',
  slate700: '#334155',
  slate800: '#1e293b',
  slate900: '#0f172a',
  white: '#ffffff',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type ServiceListItem = {
  id: string;
  name: string;
  category: string;
};

type DoctorListItem = {
  id: string;
  name: string;
  specialization: string;
  hasSchedules: boolean;
};

type DoctorScheduleApi = {
  schedule_id: number;
  doctor_id: number;
  day_of_week: string;
  start_time: string;
  end_time: string;
  max_patients: number | null;
  is_available: boolean;
};

type TimeSlot = {
  scheduleId: string;
  start: string;
  end: string;
  label: string;
  remaining: number | null;
  isFull: boolean;
};

function isValidDate(value: string): boolean {
  return /^\d{4}-\d{2}-\d{2}$/.test(value.trim());
}

function isValidTime(value: string): boolean {
  return /^\d{2}:\d{2}$/.test(value.trim());
}

function minutesFromHHMM(timeStr: string): number | null {
  const t = String(timeStr ?? '').slice(0, 5);
  if (!/^\d{2}:\d{2}$/.test(t)) return null;
  const [h, m] = t.split(':');
  const hh = Number(h);
  const mm = Number(m);
  if (!Number.isFinite(hh) || !Number.isFinite(mm)) return null;
  return hh * 60 + mm;
}

function dayKeyFromDate(dateStr: string): string {
  if (!isValidDate(dateStr)) return '';
  const d = new Date(`${dateStr}T00:00:00`);
  if (Number.isNaN(d.getTime())) return '';
  const keys = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
  return keys[d.getDay()] ?? '';
}

function formatLocalDate(d: Date): string {
  const yyyy = d.getFullYear();
  const mm = String(d.getMonth() + 1).padStart(2, '0');
  const dd = String(d.getDate()).padStart(2, '0');
  return `${yyyy}-${mm}-${dd}`;
}

function formatTimeLabel(hhmm: string): string {
  const t = String(hhmm ?? '').slice(0, 5);
  if (!/^\d{2}:\d{2}$/.test(t)) return t;
  const [hRaw, m] = t.split(':');
  const h24 = Number(hRaw);
  const ap = h24 >= 12 ? 'PM' : 'AM';
  let h12 = h24 % 12;
  if (h12 === 0) h12 = 12;
  return `${h12}:${m} ${ap}`;
}

function normalizeText(value: unknown): string {
  return String(value ?? '').trim().toLowerCase();
}

function extractServiceCategory(serviceName: string): string {
  const raw = String(serviceName ?? '').trim();
  if (!raw) return '';
  const [head] = raw.split(':');
  return normalizeText(head || raw);
}

function specializationMatches(serviceCategory: string, doctorSpecialization: string): boolean {
  const a = normalizeText(serviceCategory);
  const b = normalizeText(doctorSpecialization);
  if (!a || !b) return false;
  return b.includes(a) || a.includes(b);
}

export default function PatientBookAppointmentScreen() {
  const router = useRouter();

  const [services, setServices] = useState<ServiceListItem[]>([]);
  const [selectedServiceId, setSelectedServiceId] = useState<string>('');
  const [doctors, setDoctors] = useState<DoctorListItem[]>([]);
  const [selectedDoctorId, setSelectedDoctorId] = useState<string>('');
  const [serviceQuery, setServiceQuery] = useState('');
  const [doctorQuery, setDoctorQuery] = useState('');
  const [doctorSchedules, setDoctorSchedules] = useState<DoctorScheduleApi[]>([]);
  const [availableDates, setAvailableDates] = useState<string[]>([]);
  const [date, setDate] = useState('');
  const [time, setTime] = useState('');
  const [timeSlots, setTimeSlots] = useState<TimeSlot[]>([]);
  const [loadingSlots, setLoadingSlots] = useState(false);
  const [reason, setReason] = useState('');

  const [submitting, setSubmitting] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [needsMedicalBackground, setNeedsMedicalBackground] = useState(false);

  const selectedService = useMemo(
    () => services.find((s) => s.id === selectedServiceId) ?? null,
    [selectedServiceId, services]
  );

  const eligibleDoctors = useMemo(() => {
    if (!selectedService) return [];
    return doctors.filter((d) => {
      if (!d.hasSchedules) return false;
      return specializationMatches(selectedService.category, d.specialization);
    });
  }, [doctors, selectedService]);

  const filteredServices = useMemo(() => {
    const q = normalizeText(serviceQuery);
    if (!q) return services;
    return services.filter((s) => normalizeText(s.name).includes(q));
  }, [serviceQuery, services]);

  const filteredDoctors = useMemo(() => {
    const q = normalizeText(doctorQuery);
    if (!q) return eligibleDoctors;
    return eligibleDoctors.filter((d) => normalizeText(`${d.name} ${d.specialization}`).includes(q));
  }, [doctorQuery, eligibleDoctors]);

  const canSubmit = useMemo(() => {
    if (submitting || loading) return false;
    if (!selectedServiceId) return false;
    if (!selectedDoctorId) return false;
    if (!isValidDate(date)) return false;
    if (!isValidTime(time)) return false;
    return true;
  }, [date, loading, selectedDoctorId, selectedServiceId, submitting, time]);

  useEffect(() => {
    let cancelled = false;

    async function load() {
      setLoading(true);
      setError('');
      setSuccess('');

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          setError('Please log in again.');
          return;
        }

        const [servicesRes, doctorsRes] = await Promise.all([
          fetch(`${API_BASE_URL}/services?per_page=200`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/doctors?per_page=200`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
        ]);

        const servicesData = await servicesRes.json().catch(() => ({}));
        if (!servicesRes.ok) {
          const message =
            typeof (servicesData as any)?.message === 'string' && (servicesData as any).message.length > 0
              ? (servicesData as any).message
              : 'Unable to load services.';
          setError(message);
          return;
        }

        const doctorsData = await doctorsRes.json().catch(() => ({}));
        if (!doctorsRes.ok) {
          const message =
            typeof (doctorsData as any)?.message === 'string' && (doctorsData as any).message.length > 0
              ? (doctorsData as any).message
              : 'Unable to load doctors.';
          setError(message);
          return;
        }

        const rawServices = Array.isArray((servicesData as any)?.data)
          ? (servicesData as any).data
          : Array.isArray(servicesData)
            ? (servicesData as any)
            : [];
        const mappedServices: ServiceListItem[] = rawServices
          .map((s: any) => {
            const name = String(s?.service_name ?? s?.name ?? '').trim();
            const id = String(s?.service_id ?? s?.id ?? '').trim();
            return {
              id,
              name: name || `Service #${id || '—'}`,
              category: extractServiceCategory(name),
            };
          })
          .filter((s: ServiceListItem) => s.id.length > 0);

        const rawDoctors = Array.isArray((doctorsData as any)?.data)
          ? (doctorsData as any).data
          : Array.isArray(doctorsData)
            ? (doctorsData as any)
            : [];
        const mappedDoctors: DoctorListItem[] = rawDoctors
          .map((d: any) => {
            const first = d?.firstname ? String(d.firstname) : '';
            const last = d?.lastname ? String(d.lastname) : '';
            const name = `Dr. ${[first, last].filter(Boolean).join(' ')}`.trim();
            const schedules = Array.isArray(d?.doctor_schedules)
              ? d.doctor_schedules
              : Array.isArray(d?.doctorSchedules)
                ? d.doctorSchedules
                : [];
            return {
              id: String(d.user_id ?? d.id ?? ''),
              name: name === 'Dr.' ? 'Doctor' : name,
              specialization: String(d?.specialization ?? '').trim(),
              hasSchedules: schedules.length > 0,
            };
          })
          .filter((d: DoctorListItem) => d.id.length > 0);

        if (!cancelled) {
          setServices(mappedServices);
          setDoctors(mappedDoctors);
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    load();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    let cancelled = false;

    async function loadDoctorSchedulesForSelection() {
      setDoctorSchedules([]);
      setAvailableDates([]);
      setTimeSlots([]);
      setDate('');
      setTime('');

      if (!selectedDoctorId) return;

      setLoadingSlots(true);
      setError('');
      setSuccess('');

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          setError('Please log in again.');
          return;
        }

        const res = await fetch(`${API_BASE_URL}/doctor-schedules?doctor_id=${encodeURIComponent(selectedDoctorId)}&per_page=500`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
          const msg =
            typeof (data as any)?.message === 'string' && (data as any).message.length > 0
              ? (data as any).message
              : 'Unable to load doctor schedules.';
          setError(msg);
          return;
        }

        const raw = Array.isArray((data as any)?.data) ? (data as any).data : Array.isArray(data) ? (data as any) : [];
        const schedules: DoctorScheduleApi[] = raw as any;
        if (cancelled) return;
        setDoctorSchedules(schedules);

        const daySet = new Set<string>();
        for (const s of schedules) {
          if ((s as any)?.is_available === false) continue;
          const key = String((s as any)?.day_of_week ?? '').toLowerCase();
          if (key) daySet.add(key);
        }

        const next: string[] = [];
        const base = new Date();
        for (let i = 0; i < 21; i += 1) {
          const d = new Date(base.getFullYear(), base.getMonth(), base.getDate() + i);
          const ds = formatLocalDate(d);
          const key = dayKeyFromDate(ds);
          if (key && daySet.has(key)) {
            next.push(ds);
          }
        }

        setAvailableDates(next);
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoadingSlots(false);
      }
    }

    loadDoctorSchedulesForSelection();
    return () => {
      cancelled = true;
    };
  }, [selectedDoctorId, loading, router]);

  useEffect(() => {
    let cancelled = false;

    async function loadSlotsForDate() {
      setTime('');
      setTimeSlots([]);

      if (!selectedDoctorId || !isValidDate(date) || doctorSchedules.length === 0) return;

      setLoadingSlots(true);
      setError('');
      setSuccess('');

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          setError('Please log in again.');
          return;
        }

        const res = await fetch(
          `${API_BASE_URL}/appointments?doctor_id=${encodeURIComponent(selectedDoctorId)}&start_date=${encodeURIComponent(date)}&end_date=${encodeURIComponent(date)}&per_page=200`,
          { headers: { Accept: 'application/json', Authorization: `Bearer ${token}` } }
        );
        const data = await res.json().catch(() => ({}));
        if (!res.ok) {
          const msg =
            typeof (data as any)?.message === 'string' && (data as any).message.length > 0
              ? (data as any).message
              : 'Unable to load bookings.';
          setError(msg);
          return;
        }

        const appointments = Array.isArray((data as any)?.data) ? (data as any).data : Array.isArray(data) ? (data as any) : [];
        const apptTimes: string[] = appointments
          .filter((a: any) => String(a?.status ?? '') !== 'cancelled' && String(a?.appointment_type ?? 'scheduled') === 'scheduled')
          .map((a: any) => String(a?.appointment_datetime ?? '').replace('T', ' ').slice(0, 16))
          .filter((v: string) => /^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/.test(v))
          .map((v: string) => v.slice(11, 16));

        const dayKey = dayKeyFromDate(date);
        const daySchedules = doctorSchedules.filter((s) => (s as any)?.is_available !== false && String((s as any)?.day_of_week ?? '').toLowerCase() === dayKey);

        const slotMinutes = 90;
        const bookedSet = new Set(apptTimes);

        const intervals: Array<{ start: number; end: number }> = [];
        for (const s of daySchedules) {
          const start = String((s as any)?.start_time ?? '').slice(0, 5);
          const end = String((s as any)?.end_time ?? '').slice(0, 5);
          const startMin = minutesFromHHMM(start);
          const endMin = minutesFromHHMM(end);
          if (startMin == null || endMin == null) continue;
          if (endMin <= startMin) continue;
          intervals.push({ start: startMin, end: endMin });
        }
        intervals.sort((a, b) => a.start - b.start);

        const merged: Array<{ start: number; end: number }> = [];
        for (const it of intervals) {
          const last = merged.length ? merged[merged.length - 1] : null;
          if (!last) {
            merged.push({ start: it.start, end: it.end });
            continue;
          }
          if (it.start <= last.end) {
            last.end = Math.max(last.end, it.end);
          } else {
            merged.push({ start: it.start, end: it.end });
          }
        }

        const toHHMM = (mins: number) => {
          const h = Math.floor(mins / 60);
          const m = mins % 60;
          const hh = String(h).padStart(2, '0');
          const mm = String(m).padStart(2, '0');
          return `${hh}:${mm}`;
        };

        const slots: TimeSlot[] = [];
        for (const block of merged) {
          for (let m = block.start; m + slotMinutes <= block.end; m += slotMinutes) {
            const start = toHHMM(m);
            const end = toHHMM(m + slotMinutes);
            const isFull = bookedSet.has(start);
            slots.push({
              scheduleId: `${date}_${start}`,
              start,
              end,
              label: `${formatTimeLabel(start)}–${formatTimeLabel(end)}`,
              remaining: null,
              isFull,
            });
          }
        }

        if (!cancelled) setTimeSlots(slots);
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoadingSlots(false);
      }
    }

    loadSlotsForDate();
    return () => {
      cancelled = true;
    };
  }, [date, doctorSchedules, selectedDoctorId]);

  async function handleBook() {
    if (!canSubmit) {
      setError('Please select service, doctor, date and a time slot.');
      return;
    }

    setError('');
    setSuccess('');
    setNeedsMedicalBackground(false);
    setSubmitting(true);

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        router.replace('/screenviews/aut-landing/login-screen' as any);
        return;
      }

      const response = await fetch(`${API_BASE_URL}/appointments`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          doctor_id: Number(selectedDoctorId),
          service_id: Number(selectedServiceId),
          appointment_type: 'scheduled',
          appointment_datetime: `${date} ${time}`,
          reason_for_visit: reason.trim().length > 0 ? reason.trim() : null,
        }),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        if (response.status === 428 || data?.code === 'MEDICAL_BACKGROUND_REQUIRED') {
          setNeedsMedicalBackground(true);
          setError(
            typeof data?.message === 'string' && data.message.length > 0
              ? data.message
              : 'Medical background is required before booking.'
          );
          return;
        }
        const message =
          typeof data?.message === 'string' && data.message.length > 0
            ? data.message
            : 'Unable to book appointment.';
        setError(message);
        return;
      }

      setSuccess('Appointment requested. You can review it in your Appointments list.');
      setSelectedDoctorId('');
      setSelectedServiceId('');
      setReason('');
      setDate('');
      setTime('');
      setTimeSlots([]);
      setAvailableDates([]);
      setDoctorSchedules([]);
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />

      <View style={styles.header}>
        <View style={styles.headerInner}>
          <View>
            <View style={styles.eyebrowRow}>
              <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
              <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
            </View>
            <Text style={styles.headerTitle}>Book appointment</Text>
            <Text style={styles.headerSub}>Only available for doctors with schedules.</Text>
          </View>
          <Pressable onPress={() => router.back()} style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.7 }]}>
            <Text style={styles.headerBtnText}>Back</Text>
          </Pressable>
        </View>
      </View>

      <ScrollView
        style={styles.scroll}
        contentContainerStyle={styles.scrollContent}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
      >
        {error ? <Text style={styles.inlineError}>{error}</Text> : null}
        {success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}
        {needsMedicalBackground ? (
          <Pressable
            onPress={() => router.push('/screenviews/patient/patient-medical-background' as any)}
            style={({ pressed }) => [styles.warningButton, pressed && { opacity: 0.85 }]}
          >
            <Text style={styles.warningButtonText}>Add medical background now</Text>
          </Pressable>
        ) : null}

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Service</Text>
            </View>
            <Text style={styles.cardTitle}>Select service</Text>
            <Text style={styles.cardSubtitle}>Choose a service first to filter available doctors.</Text>
          </View>
          <View style={styles.cardBody}>
            <View style={styles.serviceList}>
              <TextInput
                value={serviceQuery}
                onChangeText={setServiceQuery}
                placeholder="Search service"
                placeholderTextColor="#9ca3af"
                autoCapitalize="none"
                style={styles.searchInput}
              />

              <View style={styles.chipWrap}>
                {filteredServices.map((s) => (
                  <Pressable
                    key={s.id}
                    onPress={() => {
                      setSelectedServiceId(s.id);
                      setSelectedDoctorId('');
                      setDoctorQuery('');
                      setError('');
                      setSuccess('');
                    }}
                    style={({ pressed }) => [
                      styles.serviceChip,
                      selectedServiceId === s.id && styles.serviceChipActive,
                      pressed && { opacity: 0.85 },
                    ]}
                  >
                    <Text style={[styles.serviceChipText, selectedServiceId === s.id && styles.serviceChipTextActive]}>
                      {s.name}
                    </Text>
                  </Pressable>
                ))}
              </View>
              {!loading && services.length === 0 ? (
                <Text style={styles.helperText}>No services found. Please try again later.</Text>
              ) : null}
            </View>
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Doctor</Text>
            </View>
            <Text style={styles.cardTitle}>Select doctor</Text>
            <Text style={styles.cardSubtitle}>
              {selectedServiceId ? 'Doctors with matching specialization and schedules appear here.' : 'Select a service first.'}
            </Text>
          </View>
          <View style={styles.cardBody}>
            <View style={styles.doctorList}>
              <TextInput
                value={doctorQuery}
                onChangeText={setDoctorQuery}
                placeholder="Search doctor"
                placeholderTextColor="#9ca3af"
                autoCapitalize="none"
                editable={!!selectedServiceId}
                style={styles.searchInput}
              />

              <View style={styles.chipWrap}>
                {filteredDoctors.map((doc) => (
                  <Pressable
                    key={doc.id}
                    onPress={() => {
                      setSelectedDoctorId(doc.id);
                      setError('');
                      setSuccess('');
                    }}
                    style={({ pressed }) => [
                      styles.doctorChip,
                      selectedDoctorId === doc.id && styles.doctorChipActive,
                      pressed && { opacity: 0.85 },
                    ]}
                  >
                    <Text style={[styles.doctorChipText, selectedDoctorId === doc.id && styles.doctorChipTextActive]}>
                      {doc.name}
                      {doc.specialization ? ` · ${doc.specialization}` : ''}
                    </Text>
                  </Pressable>
                ))}
              </View>
              {!loading && selectedServiceId && eligibleDoctors.length === 0 ? (
                <Text style={styles.helperText}>No doctors match this service right now.</Text>
              ) : null}
              {!loading && !selectedServiceId ? (
                <Text style={styles.helperText}>Select a service first to see doctors.</Text>
              ) : null}
            </View>
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Details</Text>
            </View>
            <Text style={styles.cardTitle}>Appointment details</Text>
            <Text style={styles.cardSubtitle}>Pick a date and an available time slot. Full slots are disabled.</Text>
          </View>
          <View style={styles.cardBody}>
            <Text style={styles.label}>Date</Text>
            {loadingSlots ? <Text style={styles.helperText}>Loading availability…</Text> : null}
            {!selectedDoctorId ? <Text style={styles.helperText}>Select a doctor to see available dates.</Text> : null}
            {selectedDoctorId && !loadingSlots && availableDates.length === 0 ? (
              <Text style={styles.helperText}>No available dates found for this doctor.</Text>
            ) : null}
            {availableDates.length > 0 ? (
              <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.dateRow}>
                {availableDates.map((d) => (
                  <Pressable
                    key={d}
                    onPress={() => setDate(d)}
                    style={({ pressed }) => [
                      styles.dateChip,
                      date === d && styles.dateChipActive,
                      pressed && { opacity: 0.85 },
                    ]}
                  >
                    <Text style={[styles.dateChipText, date === d && styles.dateChipTextActive]}>{d}</Text>
                  </Pressable>
                ))}
              </ScrollView>
            ) : null}

            <Text style={[styles.label, { marginTop: 12 }]}>Time slot</Text>
            {!date ? <Text style={styles.helperText}>Select a date to load time slots.</Text> : null}
            {date && timeSlots.length === 0 && !loadingSlots ? <Text style={styles.helperText}>No time slots available.</Text> : null}
            <View style={styles.slotWrap}>
              {timeSlots.map((s) => (
                <Pressable
                  key={s.scheduleId}
                  disabled={s.isFull}
                  onPress={() => setTime(s.start)}
                  style={({ pressed }) => [
                    styles.slotChip,
                    time === s.start && styles.slotChipActive,
                    s.isFull && styles.slotChipFull,
                    pressed && !s.isFull && { opacity: 0.85 },
                  ]}
                >
                  <Text
                    style={[
                      styles.slotChipText,
                      time === s.start && styles.slotChipTextActive,
                      s.isFull && styles.slotChipTextFull,
                    ]}
                  >
                    {s.label}
                    {s.isFull ? ' · Full' : s.remaining != null ? ` · ${s.remaining} left` : ''}
                  </Text>
                </Pressable>
              ))}
            </View>

            <Text style={[styles.label, { marginTop: 12 }]}>Reason (optional)</Text>
            <TextInput
              value={reason}
              onChangeText={setReason}
              placeholder="Brief reason for your visit"
              placeholderTextColor="#9ca3af"
              style={styles.input}
            />

            <Pressable
              onPress={handleBook}
              disabled={!canSubmit}
              style={({ pressed }) => [
                styles.primaryButton,
                (!canSubmit || submitting) && { opacity: 0.5 },
                pressed && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.primaryButtonText}>{submitting ? 'Submitting...' : 'Book Appointment'}</Text>
            </Pressable>

            <Pressable
              onPress={() => router.push('/screenviews/queue' as any)}
              style={({ pressed }) => [styles.secondaryButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.secondaryButtonText}>Request queue instead</Text>
            </Pressable>
          </View>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: {
    flex: 1,
    backgroundColor: T.cyan700,
  },
  header: {
    backgroundColor: T.cyan700,
    paddingHorizontal: 20,
    paddingTop: 12,
    paddingBottom: 24,
  },
  headerInner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
  },
  headerTitle: {
    fontFamily: 'serif',
    fontSize: 26,
    fontWeight: '700',
    color: T.white,
    marginBottom: 2,
    letterSpacing: 0.3,
  },
  headerSub: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.75)',
    fontWeight: '400',
  },
  headerBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.16)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  headerBtnText: {
    color: T.white,
    fontSize: 12,
    fontWeight: '600',
  },
  eyebrowRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 5,
    marginBottom: 4,
  },
  eyebrowDot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    backgroundColor: T.cyan500,
  },
  eyebrowText: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 0.9,
    textTransform: 'uppercase',
    color: T.cyan600,
  },
  scroll: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    marginTop: -16,
  },
  scrollContent: {
    paddingTop: 20,
    paddingHorizontal: 16,
    paddingBottom: 24,
  },
  warningButton: {
    borderRadius: 14,
    backgroundColor: 'rgba(245,158,11,0.12)',
    borderWidth: 1,
    borderColor: 'rgba(245,158,11,0.25)',
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  warningButtonText: {
    color: '#b45309',
    fontSize: 13,
    fontWeight: '700',
  },
  inlineError: {
    fontSize: 12,
    color: '#b91c1c',
    marginBottom: 10,
  },
  inlineSuccess: {
    fontSize: 12,
    color: '#15803d',
    marginBottom: 10,
  },
  card: {
    backgroundColor: T.white,
    borderRadius: 20,
    marginBottom: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 10,
    elevation: 2,
    overflow: 'hidden',
  },
  cardHeader: {
    paddingHorizontal: 16,
    paddingTop: 16,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  cardTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: T.slate900,
    letterSpacing: 0.1,
  },
  cardSubtitle: {
    fontSize: 11,
    color: T.slate400,
    marginTop: 2,
  },
  cardBody: {
    paddingHorizontal: 16,
    paddingTop: 12,
    paddingBottom: 14,
  },
  label: {
    fontSize: 11,
    fontWeight: '600',
    color: T.slate600,
    marginBottom: 6,
  },
  input: {
    borderRadius: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 13,
    color: T.slate800,
    backgroundColor: T.white,
  },
  searchInput: {
    width: '100%',
    borderRadius: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    paddingHorizontal: 12,
    paddingVertical: 10,
    fontSize: 13,
    color: T.slate800,
    backgroundColor: T.white,
  },
  serviceList: {
    gap: 10,
  },
  serviceChip: {
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
  },
  serviceChipActive: {
    borderColor: T.cyan600,
    backgroundColor: 'rgba(6,182,212,0.10)',
  },
  serviceChipText: {
    fontSize: 11,
    fontWeight: '600',
    color: T.slate700,
  },
  serviceChipTextActive: {
    color: T.cyan700,
  },
  doctorList: {
    gap: 10,
  },
  chipWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  doctorChip: {
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
  },
  doctorChipActive: {
    borderColor: T.cyan600,
    backgroundColor: 'rgba(6,182,212,0.10)',
  },
  doctorChipText: {
    fontSize: 11,
    fontWeight: '600',
    color: T.slate700,
  },
  doctorChipTextActive: {
    color: T.cyan700,
  },
  helperText: {
    fontSize: 11,
    color: T.slate500,
    marginTop: 10,
  },
  dateRow: {
    paddingVertical: 6,
    gap: 8,
  },
  dateChip: {
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
  },
  dateChipActive: {
    borderColor: T.cyan600,
    backgroundColor: 'rgba(6,182,212,0.10)',
  },
  dateChipText: {
    fontSize: 11,
    fontWeight: '600',
    color: T.slate700,
  },
  dateChipTextActive: {
    color: T.cyan700,
  },
  slotWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
  },
  slotChip: {
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
  },
  slotChipActive: {
    borderColor: T.cyan600,
    backgroundColor: 'rgba(6,182,212,0.10)',
  },
  slotChipFull: {
    borderColor: T.slate200,
    backgroundColor: T.slate100,
  },
  slotChipText: {
    fontSize: 11,
    fontWeight: '600',
    color: T.slate700,
  },
  slotChipTextActive: {
    color: T.cyan700,
  },
  slotChipTextFull: {
    color: T.slate400,
  },
  primaryButton: {
    marginTop: 14,
    borderRadius: 999,
    backgroundColor: '#0f766e',
    paddingVertical: 11,
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.white,
  },
  secondaryButton: {
    marginTop: 10,
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingVertical: 11,
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryButtonText: {
    fontSize: 12,
    fontWeight: '600',
    color: T.slate700,
  },
});
