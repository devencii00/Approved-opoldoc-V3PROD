import React, { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  View,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
import { useIsFocused } from '@react-navigation/native';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  amber100: 'rgba(245,158,11,0.12)',
  amber700: '#b45309',
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
  red100: 'rgba(239,68,68,0.12)',
  red700: '#b91c1c',
  green100: 'rgba(34,197,94,0.12)',
  green700: '#15803d',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');
const APP_BASE_URL = API_BASE_URL.replace(/\/api$/, '');

type RecordsTabKey = 'visits' | 'prescriptions' | 'vitals';

type CurrentUser = {
  user_id: number;
};

type DependentUser = {
  user_id: number;
  firstname: string | null;
  middlename: string | null;
  lastname: string | null;
  birthdate: string | null;
  sex: string | null;
  email: string | null;
  contact_number: string | null;
  relationship: string | null;
  is_dependent: boolean;
  account_activated: boolean;
};

type DependentAppointmentSummary = {
  id: string;
  patientId: number;
  doctor: string;
  dateTime: string;
  sortAt: number;
  reason: string;
  status: string;
  statusRaw: string;
  type: string;
  serviceNames: string[];
  totalFee: number;
  queueNumber: string | null;
};

type DependentBillingSummary = {
  id: string;
  patientId: number;
  appointmentId: string;
  amount: number;
  paymentStatus: string;
  dateTime: string;
  doctor: string;
  serviceNames: string[];
};

type DependentQueueSummary = {
  queueId: string;
  patientId: number;
  queueNumber: string;
  status: 'waiting' | 'serving' | 'done' | 'cancelled';
  doctorId: string;
  doctor: string;
  position: number | null;
  estimatedWaitMinutes: number | null;
  serviceNames: string[];
  totalFee: number;
  servingQueueNumber: string | null;
  nextQueueNumber: string | null;
};

type DependentOverview = {
  upcomingAppointment: DependentAppointmentSummary | null;
  pendingBilling: DependentBillingSummary | null;
  activeQueue: DependentQueueSummary | null;
};

type VisitHistoryItem = {
  id: string;
  date: string;
  doctor: string;
  reason: string;
  diagnosis: string;
  treatment: string;
  paymentStatus: string;
  appointmentType: string;
  prescriptionSummaries: string[];
};

type PrescriptionMedicineItem = {
  id: string;
  name: string;
  dosage: string;
  frequency: string;
  duration: string;
  instructions: string;
};

type PrescriptionHistoryItem = {
  id: string;
  date: string;
  doctor: string;
  summary: string;
  reason: string;
  prescriptionNotes: string;
  diagnosis: string;
  treatment: string;
  medicines: PrescriptionMedicineItem[];
};

type VitalHistoryItem = {
  id: string;
  recordedAt: string;
  appointmentDate: string;
  doctor: string;
  heightCm: string;
  weightKg: string;
  bloodPressure: string;
  temperature: string;
  pulseRate: string;
  bmi: string;
  bmiCategory: string;
};

type DependentRecordsState = {
  loading: boolean;
  loaded: boolean;
  error: string;
  activeTab: RecordsTabKey;
  expandedKey: string | null;
  visits: VisitHistoryItem[];
  prescriptions: PrescriptionHistoryItem[];
  vitals: VitalHistoryItem[];
};

function createRecordsState(): DependentRecordsState {
  return {
    loading: false,
    loaded: false,
    error: '',
    activeTab: 'visits',
    expandedKey: null,
    visits: [],
    prescriptions: [],
    vitals: [],
  };
}

function createDependentOverview(): DependentOverview {
  return {
    upcomingAppointment: null,
    pendingBilling: null,
    activeQueue: null,
  };
}

function normalizeText(value: any): string {
  return typeof value === 'string' ? value.trim() : '';
}

function parseNumericId(value: any): number {
  const parsed = Number(value);
  return Number.isFinite(parsed) && parsed > 0 ? parsed : 0;
}

function formatDateOnly(value: any): string {
  if (!value) return 'Not provided';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Not provided';
  return date.toLocaleDateString();
}

function formatDateTime(value: any): string {
  if (!value) return 'Date unavailable';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Date unavailable';
  return `${date.toLocaleDateString()} · ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
}

function formatCurrency(value: number): string {
  if (!Number.isFinite(value)) return 'P 0.00';
  return `P ${value.toFixed(2)}`;
}

function formatDoctorName(raw: any): string {
  const first = raw?.firstname ? String(raw.firstname) : '';
  const last = raw?.lastname ? String(raw.lastname) : '';
  const full = `Dr. ${[first, last].filter(Boolean).join(' ')}`.trim();
  return full === 'Dr.' ? 'Doctor' : full;
}

function formatAppointmentType(value: any): string {
  const raw = typeof value === 'string' ? value.trim().toLowerCase() : '';
  if (raw === 'walk_in' || raw === 'walk-in' || raw === 'walk in') return 'Walk-in';
  if (raw === 'scheduled') return 'Scheduled';
  return 'Visit';
}

function formatAppointmentStatus(value: any): string {
  const raw = normalizeText(value).toLowerCase();
  if (raw === 'confirmed') return 'Confirmed';
  if (raw === 'pending') return 'Pending';
  if (raw === 'consulted') return 'Consulted';
  if (raw === 'completed') return 'Completed';
  if (raw === 'cancelled') return 'Cancelled';
  if (raw === 'no_show') return 'No show';
  return raw ? `${raw.charAt(0).toUpperCase()}${raw.slice(1).replace(/_/g, ' ')}` : 'Unknown';
}

function formatPaymentStatus(value: any): string {
  const raw = normalizeText(value).toLowerCase();
  if (raw === 'paid') return 'Paid';
  if (raw === 'pending') return 'Pending payment';
  if (raw === 'failed') return 'Payment failed';
  return raw ? `${raw.charAt(0).toUpperCase()}${raw.slice(1).replace(/_/g, ' ')}` : 'Pending payment';
}

function formatQueueStatus(value: any): 'waiting' | 'serving' | 'done' | 'cancelled' {
  const raw = normalizeText(value).toLowerCase();
  if (raw === 'serving') return 'serving';
  if (raw === 'done') return 'done';
  if (raw === 'cancelled') return 'cancelled';
  return 'waiting';
}

function formatQueueStatusLabel(value: any): string {
  const status = formatQueueStatus(value);
  if (status === 'serving') return 'In service';
  if (status === 'done') return 'Done';
  if (status === 'cancelled') return 'Cancelled';
  return 'Waiting';
}

function extractServiceNames(services: any): string[] {
  if (!Array.isArray(services)) return [];
  return services
    .map((service: any) => normalizeText(service?.service_name || service?.name))
    .filter(Boolean);
}

function calculateServiceTotal(services: any): number {
  if (!Array.isArray(services)) return 0;
  return services.reduce((sum: number, service: any) => {
    const price = typeof service?.price === 'number' ? service.price : service?.price != null ? Number(service.price) : 0;
    return sum + (Number.isNaN(price) ? 0 : price);
  }, 0);
}

function formatNumberLabel(value: any, suffix = ''): string {
  if (value == null || value === '') return 'Not recorded';
  const numeric = Number(value);
  if (Number.isNaN(numeric)) return 'Not recorded';
  return `${numeric.toFixed(1)}${suffix}`;
}

function computeBmi(heightCmRaw: any, weightKgRaw: any): { bmi: string; category: string } {
  const heightCm = Number(heightCmRaw);
  const weightKg = Number(weightKgRaw);
  if (Number.isNaN(heightCm) || Number.isNaN(weightKg) || heightCm <= 0 || weightKg <= 0) {
    return { bmi: 'Not recorded', category: 'Unavailable' };
  }

  const heightM = heightCm / 100;
  const bmi = weightKg / (heightM * heightM);
  let category = 'Obese (30 and above)';
  if (bmi < 18.5) category = 'Underweight (Below 18.5)';
  else if (bmi < 25) category = 'Normal (18.5 - 24.9)';
  else if (bmi < 30) category = 'Overweight (25.0 - 29.9)';

  return { bmi: bmi.toFixed(1), category };
}

function formatRelationship(value: string | null): string {
  const raw = normalizeText(value).toLowerCase();
  if (raw === 'mother') return 'Mother';
  if (raw === 'father') return 'Father';
  if (raw === 'guardian') return 'Guardian';
  return 'Dependent';
}

function calculateAge(value: string | null): string {
  if (!value) return 'Age unavailable';
  const birthdate = new Date(value);
  if (Number.isNaN(birthdate.getTime())) return 'Age unavailable';

  const today = new Date();
  let age = today.getFullYear() - birthdate.getFullYear();
  const monthDiff = today.getMonth() - birthdate.getMonth();
  if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
    age -= 1;
  }

  if (age < 0) return 'Age unavailable';
  return `${age} yrs old`;
}

function formatDependentName(item: DependentUser): string {
  const fullName = [item.firstname, item.middlename, item.lastname]
    .map((part) => normalizeText(part))
    .filter(Boolean)
    .join(' ');

  return fullName || `Dependent #${item.user_id}`;
}

export default function DependentsScreen() {
  const router = useRouter();
  const isFocused = useIsFocused();
  const [currentUser, setCurrentUser] = useState<CurrentUser | null>((globalThis as any)?.currentUser ?? null);
  const [dependents, setDependents] = useState<DependentUser[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [overviewLoading, setOverviewLoading] = useState(false);
  const [overviewError, setOverviewError] = useState('');
  const [overviewByDependent, setOverviewByDependent] = useState<Record<number, DependentOverview>>({});
  const [expandedDependentId, setExpandedDependentId] = useState<number | null>(null);
  const [recordsByDependent, setRecordsByDependent] = useState<Record<number, DependentRecordsState>>({});

  useEffect(() => {
    if (!isFocused) return;

    let cancelled = false;

    async function loadDependents() {
      setLoading(true);
      setError('');
      setOverviewError('');

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        const cachedUser = (globalThis as any)?.currentUser as CurrentUser | undefined;
        const currentUserId = Number(cachedUser?.user_id ?? 0);

        if (!token || !currentUserId) {
          if (!cancelled) setError('Please log in again.');
          return;
        }

        const [userRes, dependentsRes] = await Promise.all([
          fetch(`${API_BASE_URL}/user`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/users/${currentUserId}/dependents`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
        ]);

        const [userData, dependentsData] = await Promise.all([
          userRes.json().catch(() => ({})),
          dependentsRes.json().catch(() => ({})),
        ]);

        if (!userRes.ok || !dependentsRes.ok) {
          const message = userData?.message || dependentsData?.message;
          if (!cancelled) {
            setError(typeof message === 'string' && message.length > 0 ? message : 'Unable to load dependents.');
          }
          return;
        }

        const rows = Array.isArray(dependentsData?.data) ? dependentsData.data : Array.isArray(dependentsData) ? dependentsData : [];
        const mapped: DependentUser[] = rows
          .map((row: any) => ({
            user_id: Number(row?.user_id),
            firstname: row?.firstname != null ? String(row.firstname) : null,
            middlename: row?.middlename != null ? String(row.middlename) : null,
            lastname: row?.lastname != null ? String(row.lastname) : null,
            birthdate: row?.birthdate != null ? String(row.birthdate) : null,
            sex: row?.sex != null ? String(row.sex) : null,
            email: row?.email != null ? String(row.email) : null,
            contact_number: row?.contact_number != null ? String(row.contact_number) : null,
            relationship: row?.relationship != null ? String(row.relationship) : null,
            is_dependent: !!row?.is_dependent,
            account_activated: !!row?.account_activated,
          }))
          .filter((item: DependentUser) => item.user_id > 0);

        if (!cancelled) {
          setCurrentUser({ user_id: Number(userData?.user_id ?? currentUserId) });
          setDependents(mapped);
          setOverviewByDependent(() => (
            mapped.reduce<Record<number, DependentOverview>>((acc, item) => {
              acc[item.user_id] = createDependentOverview();
              return acc;
            }, {})
          ));
          setExpandedDependentId((current) => (current && mapped.some((item) => item.user_id === current) ? current : null));
        }

        if (mapped.length === 0) {
          return;
        }

        if (!cancelled) {
          setOverviewLoading(true);
        }

        try {
          const [appointmentsRes, transactionsRes, queuesRes] = await Promise.all([
            fetch(`${API_BASE_URL}/appointments?per_page=100&order=oldest`, {
              headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
            }),
            fetch(`${API_BASE_URL}/transactions?per_page=100&order=latest`, {
              headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
            }),
            fetch(`${API_BASE_URL}/queues?per_page=100`, {
              headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
            }),
          ]);

          const [appointmentsData, transactionsData, queuesData] = await Promise.all([
            appointmentsRes.json().catch(() => ({})),
            transactionsRes.json().catch(() => ({})),
            queuesRes.json().catch(() => ({})),
          ]);

          if (!appointmentsRes.ok || !transactionsRes.ok || !queuesRes.ok) {
            const message = appointmentsData?.message || transactionsData?.message || queuesData?.message;
            if (!cancelled) {
              setOverviewError(typeof message === 'string' && message.length > 0 ? message : 'Unable to load dependent care overview.');
            }
            return;
          }

          const dependentIds = new Set(mapped.map((item) => item.user_id));
          const appointmentRows = Array.isArray(appointmentsData?.data) ? appointmentsData.data : [];
          const transactionRows = Array.isArray(transactionsData?.data) ? transactionsData.data : [];
          const queueRows = Array.isArray(queuesData?.data) ? queuesData.data : [];

          const appointments = appointmentRows
            .map((row: any): DependentAppointmentSummary | null => {
              const patientId = parseNumericId(row?.patient_id ?? row?.patient?.user_id);
              if (!dependentIds.has(patientId)) return null;

              const rawDate = row?.appointment_datetime ? new Date(row.appointment_datetime) : null;
              const sortAt = rawDate && !Number.isNaN(rawDate.getTime()) ? rawDate.getTime() : 0;
              return {
                id: String(row?.appointment_id ?? ''),
                patientId,
                doctor: row?.doctor ? formatDoctorName(row.doctor) : 'Doctor',
                dateTime: formatDateTime(row?.appointment_datetime),
                sortAt,
                reason: normalizeText(row?.reason_for_visit) || 'Reason for visit not provided.',
                status: formatAppointmentStatus(row?.status),
                statusRaw: normalizeText(row?.status).toLowerCase(),
                type: formatAppointmentType(row?.appointment_type),
                serviceNames: extractServiceNames(row?.services),
                totalFee: calculateServiceTotal(row?.services),
                queueNumber: row?.queue?.queue_number != null ? String(row.queue.queue_number) : null,
              };
            })
            .filter((item: DependentAppointmentSummary | null): item is DependentAppointmentSummary => Boolean(item?.id));

          const transactions = transactionRows
            .map((row: any): DependentBillingSummary | null => {
              const patientId = parseNumericId(row?.appointment?.patient_id ?? row?.appointment?.patient?.user_id);
              if (!dependentIds.has(patientId)) return null;

              const appointmentServices = Array.isArray(row?.appointment?.services) ? row.appointment.services : [];
              const fallbackAmount = calculateServiceTotal(appointmentServices);
              const rawAmount = typeof row?.amount === 'number' ? row.amount : row?.amount != null ? Number(row.amount) : fallbackAmount;
              return {
                id: String(row?.transaction_id ?? ''),
                patientId,
                appointmentId: row?.appointment_id != null
                  ? String(row.appointment_id)
                  : row?.appointment?.appointment_id != null
                    ? String(row.appointment.appointment_id)
                    : '',
                amount: Number.isNaN(rawAmount) ? fallbackAmount : rawAmount,
                paymentStatus: normalizeText(row?.payment_status).toLowerCase(),
                dateTime: formatDateTime(row?.transaction_datetime ?? row?.visit_datetime ?? row?.created_at),
                doctor: row?.appointment?.doctor ? formatDoctorName(row.appointment.doctor) : 'Doctor',
                serviceNames: extractServiceNames(appointmentServices),
              };
            })
            .filter((item: DependentBillingSummary | null): item is DependentBillingSummary => Boolean(item?.id));

          const queues = queueRows
            .map((row: any): DependentQueueSummary | null => {
              const patientId = parseNumericId(row?.appointment?.patient_id ?? row?.appointment?.patient?.user_id);
              if (!dependentIds.has(patientId)) return null;

              return {
                queueId: String(row?.queue_id ?? ''),
                patientId,
                queueNumber: row?.queue_number != null ? String(row.queue_number) : '',
                status: formatQueueStatus(row?.status),
                doctorId: row?.appointment?.doctor_id != null ? String(row.appointment.doctor_id) : '',
                doctor: row?.appointment?.doctor ? formatDoctorName(row.appointment.doctor) : 'Doctor',
                position: typeof row?.position === 'number' ? row.position : row?.position != null ? Number(row.position) : null,
                estimatedWaitMinutes: typeof row?.estimated_wait_minutes === 'number'
                  ? row.estimated_wait_minutes
                  : row?.estimated_wait_minutes != null
                    ? Number(row.estimated_wait_minutes)
                    : null,
                serviceNames: extractServiceNames(row?.appointment?.services),
                totalFee: calculateServiceTotal(row?.appointment?.services),
                servingQueueNumber: null,
                nextQueueNumber: null,
              };
            })
            .filter((item: DependentQueueSummary | null): item is DependentQueueSummary => Boolean(item?.queueId))
            .filter((item: DependentQueueSummary) => item.status === 'waiting' || item.status === 'serving');

          const doctorIds: string[] = Array.from(
            new Set(
              queues
                .map((item: DependentQueueSummary) => item.doctorId)
                .filter((doctorId: string): doctorId is string => doctorId.length > 0)
            )
          );
          const queueBoardEntries = await Promise.all(
            doctorIds.map(async (doctorId: string) => {
              try {
                const response = await fetch(`${APP_BASE_URL}/queue-display/data?doctor_id=${encodeURIComponent(doctorId)}`, {
                  headers: { Accept: 'application/json' },
                });
                const data = await response.json().catch(() => ({}));
                if (!response.ok) return null;
                const nowServing = Array.isArray(data?.now_serving) ? data.now_serving[0] : null;
                const nextInLine = Array.isArray(data?.next) ? data.next[0] : null;
                return {
                  doctorId,
                  servingQueueNumber: nowServing?.queue_number != null ? String(nowServing.queue_number) : null,
                  nextQueueNumber: nextInLine?.queue_number != null ? String(nextInLine.queue_number) : null,
                };
              } catch {
                return null;
              }
            })
          );

          const boardByDoctor = new Map<string, { servingQueueNumber: string | null; nextQueueNumber: string | null }>();
          queueBoardEntries.forEach((entry) => {
            if (!entry) return;
            boardByDoctor.set(entry.doctorId, {
              servingQueueNumber: entry.servingQueueNumber,
              nextQueueNumber: entry.nextQueueNumber,
            });
          });

          const nextOverview: Record<number, DependentOverview> = mapped.reduce((acc, item) => {
            const dependentAppointments = appointments
              .filter((appointment: DependentAppointmentSummary) => appointment.patientId === item.user_id)
              .sort((a: DependentAppointmentSummary, b: DependentAppointmentSummary) => a.sortAt - b.sortAt);
            const dependentTransactions = transactions.filter((transaction: DependentBillingSummary) => transaction.patientId === item.user_id);
            const dependentQueue = queues.find((queue: DependentQueueSummary) => queue.patientId === item.user_id) ?? null;
            const queueBoard = dependentQueue?.doctorId ? boardByDoctor.get(dependentQueue.doctorId) : null;

            acc[item.user_id] = {
              upcomingAppointment: dependentAppointments.find((appointment: DependentAppointmentSummary) => (
                appointment.sortAt >= Date.now() &&
                (appointment.statusRaw === 'pending' || appointment.statusRaw === 'confirmed')
              )) ?? null,
              pendingBilling: dependentTransactions.find((transaction: DependentBillingSummary) => transaction.paymentStatus !== 'paid') ?? null,
              activeQueue: dependentQueue
                ? {
                    ...dependentQueue,
                    servingQueueNumber: queueBoard?.servingQueueNumber ?? null,
                    nextQueueNumber: queueBoard?.nextQueueNumber ?? null,
                  }
                : null,
            };
            return acc;
          }, {} as Record<number, DependentOverview>);

          if (!cancelled) {
            setOverviewByDependent(nextOverview);
            setOverviewError('');
          }
        } catch {
          if (!cancelled) {
            setOverviewError('Network error while loading dependent care overview.');
          }
        } finally {
          if (!cancelled) {
            setOverviewLoading(false);
          }
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    void loadDependents();
    return () => {
      cancelled = true;
    };
  }, [isFocused]);

  const dependentCountLabel = useMemo(() => {
    if (loading) return 'Loading linked dependents...';
    if (dependents.length === 0) return 'No linked dependent accounts found.';
    return `${dependents.length} linked dependent${dependents.length === 1 ? '' : 's'}`;
  }, [dependents.length, loading]);

  const overviewCounts = useMemo(() => {
    return dependents.reduce(
      (acc, dependent) => {
        const overview = overviewByDependent[dependent.user_id] ?? createDependentOverview();
        if (overview.upcomingAppointment) acc.upcoming += 1;
        if (overview.pendingBilling) acc.billing += 1;
        if (overview.activeQueue) acc.queue += 1;
        return acc;
      },
      { upcoming: 0, billing: 0, queue: 0 }
    );
  }, [dependents, overviewByDependent]);

  function updateRecordsState(
    dependentId: number,
    updater: (current: DependentRecordsState) => DependentRecordsState
  ) {
    setRecordsByDependent((current) => {
      const existing = current[dependentId] ?? createRecordsState();
      return {
        ...current,
        [dependentId]: updater(existing),
      };
    });
  }

  async function loadDependentRecords(dependentId: number) {
    updateRecordsState(dependentId, (current) => ({
      ...current,
      loading: true,
      error: '',
    }));

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token || !currentUser?.user_id) {
        updateRecordsState(dependentId, (current) => ({
          ...current,
          loading: false,
          error: 'Please log in again.',
        }));
        return;
      }

      const query = `patient_id=${encodeURIComponent(String(dependentId))}&per_page=100`;
      const [visitsRes, prescriptionsRes, vitalsRes] = await Promise.all([
        fetch(`${API_BASE_URL}/visits?${query}`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        }),
        fetch(`${API_BASE_URL}/prescriptions?${query}`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        }),
        fetch(`${API_BASE_URL}/vitals?${query}`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        }),
      ]);

      const [visitsData, prescriptionsData, vitalsData] = await Promise.all([
        visitsRes.json().catch(() => ({})),
        prescriptionsRes.json().catch(() => ({})),
        vitalsRes.json().catch(() => ({})),
      ]);

      if (!visitsRes.ok || !prescriptionsRes.ok || !vitalsRes.ok) {
        const message = visitsData?.message || prescriptionsData?.message || vitalsData?.message;
        updateRecordsState(dependentId, (current) => ({
          ...current,
          loading: false,
          loaded: false,
          error: typeof message === 'string' && message.length > 0 ? message : 'Unable to load dependent records.',
        }));
        return;
      }

      const visitRows = Array.isArray(visitsData?.data) ? visitsData.data : [];
      const prescriptionRows = Array.isArray(prescriptionsData?.data) ? prescriptionsData.data : [];
      const vitalRows = Array.isArray(vitalsData?.data) ? vitalsData.data : [];

      const mappedVisits: VisitHistoryItem[] = visitRows
        .map((row: any) => {
          const doctor = row?.appointment?.doctor
            ? formatDoctorName(row.appointment.doctor)
            : row?.prescriptions?.[0]?.doctor
              ? formatDoctorName(row.prescriptions[0].doctor)
              : 'Doctor';

          const prescriptionSummaries = (Array.isArray(row?.prescriptions) ? row.prescriptions : [])
            .flatMap((prescription: any) => (Array.isArray(prescription?.items) ? prescription.items : []))
            .map((item: any) => normalizeText(item?.medicine_name || item?.medicine?.medicine_name))
            .filter(Boolean);

          return {
            id: String(row?.transaction_id ?? ''),
            date: formatDateTime(row?.visit_datetime ?? row?.transaction_datetime ?? row?.appointment?.appointment_datetime),
            doctor,
            reason: normalizeText(row?.appointment?.reason_for_visit) || 'Clinic visit',
            diagnosis: normalizeText(row?.diagnosis) || 'No diagnosis recorded.',
            treatment: normalizeText(row?.treatment_notes) || 'No treatment notes recorded.',
            paymentStatus: normalizeText(row?.payment_status) || 'Unknown',
            appointmentType: formatAppointmentType(row?.appointment?.appointment_type),
            prescriptionSummaries: Array.from(new Set(prescriptionSummaries)),
          };
        })
        .filter((item: VisitHistoryItem) => item.id.length > 0);

      const mappedPrescriptions: PrescriptionHistoryItem[] = prescriptionRows
        .map((row: any) => {
          const medicines: PrescriptionMedicineItem[] = (Array.isArray(row?.items) ? row.items : []).map((item: any) => ({
            id: String(item?.item_id ?? `${row?.prescription_id ?? 'rx'}-${item?.medicine_id ?? Math.random()}`),
            name: normalizeText(item?.medicine_name || item?.medicine?.medicine_name) || 'Medicine',
            dosage: normalizeText(item?.dosage),
            frequency: normalizeText(item?.frequency),
            duration: normalizeText(item?.duration),
            instructions: normalizeText(item?.instructions),
          }));

          return {
            id: String(row?.prescription_id ?? ''),
            date: formatDateTime(row?.prescribed_datetime ?? row?.transaction?.visit_datetime ?? row?.transaction?.transaction_datetime),
            doctor: row?.doctor ? formatDoctorName(row.doctor) : 'Doctor',
            summary: medicines[0]?.name ?? 'Prescription',
            reason: normalizeText(row?.transaction?.appointment?.reason_for_visit) || 'Clinic visit',
            prescriptionNotes: normalizeText(row?.notes) || 'No prescription notes recorded.',
            diagnosis: normalizeText(row?.transaction?.diagnosis) || 'No diagnosis recorded.',
            treatment: normalizeText(row?.transaction?.treatment_notes) || 'No treatment notes recorded.',
            medicines,
          };
        })
        .filter((item: PrescriptionHistoryItem) => item.id.length > 0);

      const mappedVitals: VitalHistoryItem[] = vitalRows
        .map((row: any) => {
          const bmi = computeBmi(row?.height_cm, row?.weight_kg);
          const doctorFull = `Dr. ${[
            row?.doctor_firstname ? String(row.doctor_firstname) : '',
            row?.doctor_lastname ? String(row.doctor_lastname) : '',
          ].filter(Boolean).join(' ')}`.trim();

          return {
            id: String(row?.vital_id ?? ''),
            recordedAt: formatDateTime(row?.recorded_at),
            appointmentDate: row?.appointment_datetime ? formatDateTime(row.appointment_datetime) : 'No linked appointment',
            doctor: doctorFull === 'Dr.' ? 'Doctor' : doctorFull,
            heightCm: formatNumberLabel(row?.height_cm, ' cm'),
            weightKg: formatNumberLabel(row?.weight_kg, ' kg'),
            bloodPressure: normalizeText(row?.blood_pressure) || 'Not recorded',
            temperature: formatNumberLabel(row?.temperature, ' C'),
            pulseRate: row?.pulse_rate != null && row?.pulse_rate !== '' ? `${row.pulse_rate} bpm` : 'Not recorded',
            bmi: bmi.bmi,
            bmiCategory: bmi.category,
          };
        })
        .filter((item: VitalHistoryItem) => item.id.length > 0);

      updateRecordsState(dependentId, (current) => ({
        ...current,
        loading: false,
        loaded: true,
        error: '',
        visits: mappedVisits,
        prescriptions: mappedPrescriptions,
        vitals: mappedVitals,
      }));
    } catch {
      updateRecordsState(dependentId, (current) => ({
        ...current,
        loading: false,
        loaded: false,
        error: 'Network error. Please try again.',
      }));
    }
  }

  function handleToggleRecords(dependentId: number) {
    setExpandedDependentId((current) => (current === dependentId ? null : dependentId));

    const state = recordsByDependent[dependentId];
    if (!state || (!state.loaded && !state.loading)) {
      void loadDependentRecords(dependentId);
    }
  }

  function handleSelectTab(dependentId: number, tab: RecordsTabKey) {
    updateRecordsState(dependentId, (current) => ({
      ...current,
      activeTab: tab,
      expandedKey: null,
    }));
  }

  function handleToggleItem(dependentId: number, recordKey: string) {
    updateRecordsState(dependentId, (current) => ({
      ...current,
      expandedKey: current.expandedKey === recordKey ? null : recordKey,
    }));
  }

  function renderVisits(dependentId: number, state: DependentRecordsState) {
    if (state.visits.length === 0) {
      return <Text style={styles.emptyRecordsText}>No visit history found.</Text>;
    }

    return state.visits.map((item) => {
      const expanded = state.expandedKey === `visit-${item.id}`;
      return (
        <View key={item.id} style={styles.recordCard}>
          <View style={styles.recordTopRow}>
            <View style={styles.recordMain}>
              <Text style={styles.recordTitle}>{item.reason}</Text>
              <Text style={styles.recordSubtitle}>{`${item.date} · ${item.doctor}`}</Text>
              <Text style={styles.recordMeta}>{`${item.appointmentType} · Payment ${item.paymentStatus}`}</Text>
            </View>
            <Pressable
              onPress={() => handleToggleItem(dependentId, `visit-${item.id}`)}
              style={({ pressed }) => [styles.detailButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.detailButtonText}>{expanded ? 'Hide details' : 'View details'}</Text>
            </Pressable>
          </View>
          {expanded ? (
            <View style={styles.detailPanel}>
              <Text style={styles.detailLabel}>Diagnosis</Text>
              <Text style={styles.detailValue}>{item.diagnosis}</Text>
              <Text style={styles.detailLabel}>Treatment</Text>
              <Text style={styles.detailValue}>{item.treatment}</Text>
              <Text style={styles.detailLabel}>Prescription history</Text>
              <Text style={styles.detailValue}>
                {item.prescriptionSummaries.length > 0 ? item.prescriptionSummaries.join(', ') : 'No medicines recorded for this visit.'}
              </Text>
            </View>
          ) : null}
        </View>
      );
    });
  }

  function renderPrescriptions(dependentId: number, state: DependentRecordsState) {
    if (state.prescriptions.length === 0) {
      return <Text style={styles.emptyRecordsText}>No prescription history found.</Text>;
    }

    return state.prescriptions.map((item) => {
      const expanded = state.expandedKey === `prescription-${item.id}`;
      return (
        <View key={item.id} style={styles.recordCard}>
          <View style={styles.recordTopRow}>
            <View style={styles.recordMain}>
              <Text style={styles.recordTitle}>{item.summary}</Text>
              <Text style={styles.recordSubtitle}>{`${item.date} · ${item.doctor}`}</Text>
              <Text style={styles.recordMeta}>{item.reason}</Text>
            </View>
            <Pressable
              onPress={() => handleToggleItem(dependentId, `prescription-${item.id}`)}
              style={({ pressed }) => [styles.detailButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.detailButtonText}>{expanded ? 'Hide details' : 'View details'}</Text>
            </Pressable>
          </View>
          {expanded ? (
            <View style={styles.detailPanel}>
              <Text style={styles.detailLabel}>Consultation diagnosis</Text>
              <Text style={styles.detailValue}>{item.diagnosis}</Text>
              <Text style={styles.detailLabel}>Consultation treatment</Text>
              <Text style={styles.detailValue}>{item.treatment}</Text>
              <Text style={styles.detailLabel}>Prescription notes</Text>
              <Text style={styles.detailValue}>{item.prescriptionNotes}</Text>
              <Text style={styles.detailLabel}>Medicines</Text>
              {item.medicines.length > 0 ? (
                item.medicines.map((medicine) => (
                  <View key={medicine.id} style={styles.medicineRow}>
                    <Text style={styles.medicineName}>{medicine.name}</Text>
                    <Text style={styles.medicineMeta}>
                      {[medicine.dosage, medicine.frequency, medicine.duration].filter(Boolean).join(' · ') || 'No dosage details'}
                    </Text>
                    {medicine.instructions ? <Text style={styles.medicineInstructions}>{medicine.instructions}</Text> : null}
                  </View>
                ))
              ) : (
                <Text style={styles.detailValue}>No medicine items recorded.</Text>
              )}
            </View>
          ) : null}
        </View>
      );
    });
  }

  function renderVitals(dependentId: number, state: DependentRecordsState) {
    if (state.vitals.length === 0) {
      return <Text style={styles.emptyRecordsText}>No vitals history found.</Text>;
    }

    return state.vitals.map((item) => {
      const expanded = state.expandedKey === `vital-${item.id}`;
      return (
        <View key={item.id} style={styles.recordCard}>
          <View style={styles.recordTopRow}>
            <View style={styles.recordMain}>
              <Text style={styles.recordTitle}>{item.recordedAt}</Text>
              <Text style={styles.recordSubtitle}>{`${item.doctor} · BMI ${item.bmi}`}</Text>
              <Text style={styles.recordMeta}>{`${item.bloodPressure} · ${item.temperature} · ${item.pulseRate}`}</Text>
            </View>
            <Pressable
              onPress={() => handleToggleItem(dependentId, `vital-${item.id}`)}
              style={({ pressed }) => [styles.detailButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.detailButtonText}>{expanded ? 'Hide details' : 'View details'}</Text>
            </Pressable>
          </View>
          {expanded ? (
            <View style={styles.detailPanel}>
              <Text style={styles.detailLabel}>Appointment date</Text>
              <Text style={styles.detailValue}>{item.appointmentDate}</Text>
              <Text style={styles.detailLabel}>Height / Weight</Text>
              <Text style={styles.detailValue}>{`${item.heightCm} · ${item.weightKg}`}</Text>
              <Text style={styles.detailLabel}>Blood pressure / Temperature / Pulse</Text>
              <Text style={styles.detailValue}>{`${item.bloodPressure} · ${item.temperature} · ${item.pulseRate}`}</Text>
              <Text style={styles.detailLabel}>BMI category</Text>
              <Text style={styles.detailValue}>{item.bmiCategory}</Text>
            </View>
          ) : null}
        </View>
      );
    });
  }

  function renderActiveRecords(dependentId: number, state: DependentRecordsState) {
    if (state.loading) {
      return (
        <View style={styles.loadingBox}>
          <ActivityIndicator size="small" color={T.cyan700} />
          <Text style={styles.loadingText}>Loading records...</Text>
        </View>
      );
    }

    if (state.error) {
      return <Text style={styles.inlineError}>{state.error}</Text>;
    }

    if (state.activeTab === 'visits') return renderVisits(dependentId, state);
    if (state.activeTab === 'prescriptions') return renderPrescriptions(dependentId, state);
    return renderVitals(dependentId, state);
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <View style={styles.header}>
        
  <View style={styles.circleTopRight} />
          <View style={styles.circleBottomLeft} />
          <View style={styles.circleMidLeft} />
   
 <View style={styles.eyebrowRow}>
              <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
              <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
            </View>

        <Text style={styles.headerTitle}>Dependents</Text>
        <Text style={styles.subtitle}>View linked dependent accounts and their records.</Text>
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {error ? <Text style={styles.inlineError}>{error}</Text> : null}
        {overviewError ? <Text style={styles.inlineError}>{overviewError}</Text> : null}

        <View style={styles.summaryCard}>
          <View style={styles.summaryHeaderRow}>
            <View style={styles.iconWrap}>
              <Ionicons name="people-outline" size={24} color={T.cyan700} />
            </View>
            <View style={styles.summaryMain}>
              <Text style={styles.cardTitle}>Guardian care overview</Text>
              <Text style={styles.cardText}>{dependentCountLabel}</Text>
            </View>
          </View>


          {overviewLoading ? (
            <View style={styles.summaryLoadingRow}>
              <ActivityIndicator size="small" color={T.cyan700} />
              <Text style={styles.loadingText}>Refreshing dependent care status...</Text>
            </View>
          ) : null}
        </View>

        {loading ? (
          <View style={styles.loadingBox}>
            <ActivityIndicator size="small" color={T.cyan700} />
            <Text style={styles.loadingText}>Loading dependents...</Text>
          </View>
        ) : null}

        {!loading && dependents.length === 0 ? (
          <View style={styles.emptyCard}>
            <View style={styles.emptyIconWrap}>
              <Ionicons name="person-add-outline" size={24} color={T.cyan700} />
            </View>
            <Text style={styles.emptyTitle}>No dependents linked</Text>
            <Text style={styles.emptyText}>This account does not have any dependent profile linked yet.</Text>
          </View>
        ) : null}

        {!loading
          ? dependents.map((dependent) => {
              const isExpanded = expandedDependentId === dependent.user_id;
              const state = recordsByDependent[dependent.user_id] ?? createRecordsState();
              const name = formatDependentName(dependent);
              const overview = overviewByDependent[dependent.user_id] ?? createDependentOverview();
              const appointment = overview.upcomingAppointment;
              const pendingBilling = overview.pendingBilling;
              const activeQueue = overview.activeQueue;
              return (
                <View key={dependent.user_id} style={styles.dependentCard}>
                  <View style={styles.dependentCardTop}>
                    <View style={styles.dependentMain}>
                      <View style={styles.nameRow}>
                        <Text style={styles.dependentName}>{name}</Text>
                        <View style={styles.relationshipPill}>
                          <Text style={styles.relationshipText}>{formatRelationship(dependent.relationship)}</Text>
                        </View>
                      </View>
                      <Text style={styles.dependentSubtext}>{`${formatDateOnly(dependent.birthdate)} · ${calculateAge(dependent.birthdate)}`}</Text>
                    </View>
                    <View
                      style={[
                        styles.statusPill,
                        dependent.account_activated ? styles.statusPillActive : styles.statusPillPending,
                      ]}
                    >
                      <Text
                        style={[
                          styles.statusPillText,
                          dependent.account_activated ? styles.statusPillTextActive : styles.statusPillTextPending,
                        ]}
                      >
                        {dependent.account_activated ? 'Active' : 'Needs activation'}
                      </Text>
                    </View>
                  </View>

                  <View style={styles.badgeRow}>
                    {appointment ? (
                      <View style={styles.alertBadge}>
                        <Ionicons name="calendar-outline" size={13} color={T.cyan700} />
                        <Text style={styles.alertBadgeText}>Upcoming appointment</Text>
                      </View>
                    ) : null}
                    {pendingBilling ? (
                      <View style={[styles.alertBadge, styles.alertBadgeWarning]}>
                        <Ionicons name="receipt-outline" size={13} color={T.amber700} />
                        <Text style={[styles.alertBadgeText, styles.alertBadgeTextWarning]}>Pending bill</Text>
                      </View>
                    ) : null}
                    {activeQueue ? (
                      <View style={[styles.alertBadge, styles.alertBadgeSuccess]}>
                        <Ionicons name="people-outline" size={13} color={T.green700} />
                        <Text style={[styles.alertBadgeText, styles.alertBadgeTextSuccess]}>
                          {`Queue ${formatQueueStatusLabel(activeQueue.status)}`}
                        </Text>
                      </View>
                    ) : null}
                  </View>

                  <View style={styles.careOverviewGrid}>
                    <View style={styles.careOverviewCard}>
                      <Text style={styles.infoLabel}>Upcoming</Text>
                      <Text style={styles.carePrimaryText}>
                        {appointment ? appointment.dateTime : 'No appointments'}
                      </Text>
                      <Text style={styles.careSecondaryText}>
                        {appointment
                          ? `${appointment.doctor} · ${appointment.type} · ${appointment.status}`
                          : '_ _'}
                      </Text>
                      {appointment?.serviceNames.length ? (
                        <Text style={styles.careMetaText}>{appointment.serviceNames.join(', ')}</Text>
                      ) : null}
                    </View>

                    <View style={styles.careOverviewCard}>
                      <Text style={styles.infoLabel}>Pending bills</Text>
                      <Text style={styles.carePrimaryText}>
                        {pendingBilling ? formatCurrency(pendingBilling.amount) : 'No unpaid bill'}
                      </Text>
                      <Text style={styles.careSecondaryText}>
                        {pendingBilling
                          ? `${pendingBilling.doctor} · ${formatPaymentStatus(pendingBilling.paymentStatus)}`
                          : '_ _'}
                      </Text>
                      {pendingBilling?.serviceNames.length ? (
                        <Text style={styles.careMetaText}>{pendingBilling.serviceNames.join(', ')}</Text>
                      ) : null}
                    </View>

                    <View style={[styles.careOverviewCard, styles.careOverviewCardFull]}>
                      <Text style={styles.infoLabel}>Current queue</Text>
                      <Text style={styles.carePrimaryText}>
                        {activeQueue ? `Queue #${activeQueue.queueNumber || '---'}` : 'Not in queue'}
                      </Text>
                      <Text style={styles.careSecondaryText}>
                        {activeQueue
                          ? `${activeQueue.doctor} · ${formatQueueStatusLabel(activeQueue.status)}${activeQueue.position != null ? ` · Position ${activeQueue.position}` : ''}`
                          : 'Join the queue to track waiting status and line position.'}
                      </Text>

                      {activeQueue ? (
                        <View style={styles.queueOverviewMetrics}>
                          <View style={styles.queueMetricCard}>
                            <Text style={styles.queueMetricLabel}>Estimated wait</Text>
                            <Text style={styles.queueMetricText}>
                              {activeQueue.estimatedWaitMinutes != null ? `${activeQueue.estimatedWaitMinutes} mins` : 'Updating'}
                            </Text>
                          </View>
                          <View style={styles.queueMetricCard}>
                            <Text style={styles.queueMetricLabel}>Now serving</Text>
                            <Text style={styles.queueMetricText}>
                              {activeQueue.servingQueueNumber ? `#${activeQueue.servingQueueNumber}` : 'Not available'}
                            </Text>
                          </View>
                          <View style={styles.queueMetricCard}>
                            <Text style={styles.queueMetricLabel}>Next in line</Text>
                            <Text style={styles.queueMetricText}>
                              {activeQueue.nextQueueNumber ? `#${activeQueue.nextQueueNumber}` : 'Waiting'}
                            </Text>
                          </View>
                          <View style={styles.queueMetricCard}>
                            <Text style={styles.queueMetricLabel}>Consultation fee</Text>
                            <Text style={styles.queueMetricText}>{formatCurrency(activeQueue.totalFee)}</Text>
                          </View>
                        </View>
                      ) : null}

                      {activeQueue?.serviceNames.length ? (
                        <Text style={styles.careMetaText}>{activeQueue.serviceNames.join(', ')}</Text>
                      ) : null}
                    </View>
                  </View>

                  <View style={styles.infoGrid}>
                    <View style={styles.infoItem}>
                      <Text style={styles.infoLabel}>Sex</Text>
                      <Text style={styles.infoValue}>{normalizeText(dependent.sex) || 'Not provided'}</Text>
                    </View>
                    <View style={styles.infoItem}>
                      <Text style={styles.infoLabel}>Contact</Text>
                      <Text style={styles.infoValue}>{normalizeText(dependent.contact_number) || 'Not provided'}</Text>
                    </View>
                    <View style={styles.infoItemFull}>
                      <Text style={styles.infoLabel}>Dependent tracking</Text>
                      <Text style={styles.infoValue}>
                        {appointment
                          ? `Dependent appointment: appointment for "${name}".`
                          : activeQueue
                            ? `Queue tracking enabled for "${name}".`
                            : `No active appointment or queue entry for "${name}" yet.`}
                      </Text>
                    </View>
                  </View>

                  <View style={styles.actionRow}>
                    <Pressable
                      onPress={() => router.push({
                        pathname: '/screenviews/queue',
                        params: {
                          patient_id: String(dependent.user_id),
                          patient_name: name,
                        },
                      } as any)}
                      style={({ pressed }) => [styles.primaryButton, styles.actionButton, pressed && { opacity: 0.85 }]}
                    >
                      <Text style={styles.primaryButtonText}>Join Queue</Text>
                    </Pressable>

                    <Pressable
                      onPress={() => router.push({
                        pathname: '/screenviews/booking',
                        params: {
                          patient_id: String(dependent.user_id),
                          patient_name: name,
                        },
                      } as any)}
                      style={({ pressed }) => [styles.secondaryButton, styles.actionButton, pressed && { opacity: 0.85 }]}
                    >
                      <Text style={styles.secondaryButtonText}>Create Appointment</Text>
                    </Pressable>
                  </View>

                  <View style={styles.actionRow}>
                    <Pressable
                      onPress={() => router.push('/screenviews/appointments' as any)}
                      style={({ pressed }) => [styles.ghostButton, styles.actionButton, pressed && { opacity: 0.85 }]}
                    >
                      <Text style={styles.ghostButtonText}>Track Appointments</Text>
                    </Pressable>

                    <Pressable
                      onPress={() => handleToggleRecords(dependent.user_id)}
                      style={({ pressed }) => [styles.ghostButton, styles.actionButton, pressed && { opacity: 0.85 }]}
                    >
                      <Text style={styles.ghostButtonText}>{isExpanded ? 'Hide Records' : 'View Records'}</Text>
                    </Pressable>
                  </View>

                  {isExpanded ? (
                    <View style={styles.recordsSection}>
                      <View style={styles.tabRow}>
                        <Pressable
                          onPress={() => handleSelectTab(dependent.user_id, 'visits')}
                          style={[
                            styles.tabButton,
                            state.activeTab === 'visits' && styles.tabButtonActive,
                          ]}
                        >
                          <Text style={[styles.tabButtonText, state.activeTab === 'visits' && styles.tabButtonTextActive]}>
                            {`Visits (${state.visits.length})`}
                          </Text>
                        </Pressable>
                        <Pressable
                          onPress={() => handleSelectTab(dependent.user_id, 'vitals')}
                          style={[
                            styles.tabButton,
                            state.activeTab === 'vitals' && styles.tabButtonActive,
                          ]}
                        >
                          <Text style={[styles.tabButtonText, state.activeTab === 'vitals' && styles.tabButtonTextActive]}>
                            {`Vitals (${state.vitals.length})`}
                          </Text>
                        </Pressable>
                        <Pressable
                          onPress={() => handleSelectTab(dependent.user_id, 'prescriptions')}
                          style={[
                            styles.tabButton,
                            state.activeTab === 'prescriptions' && styles.tabButtonActive,
                          ]}
                        >
                          <Text
                            style={[
                              styles.tabButtonText,
                              state.activeTab === 'prescriptions' && styles.tabButtonTextActive,
                            ]}
                          >
                            {`Prescriptions (${state.prescriptions.length})`}
                          </Text>
                        </Pressable>
                      </View>

                      <View style={styles.recordsCard}>
                        <View style={styles.recordsHeader}>
                          <Text style={styles.recordsTitle}>
                            {state.activeTab === 'visits'
                              ? 'Visit history'
                              : state.activeTab === 'vitals'
                                ? 'Vitals history'
                                : 'Prescriptions history'}
                          </Text>
                          {!state.loading ? (
                            <Text style={styles.recordsSubtitle}>Linked to dependent account #{dependent.user_id}</Text>
                          ) : null}
                        </View>
                        <View style={styles.recordsBody}>{renderActiveRecords(dependent.user_id, state)}</View>
                      </View>
                    </View>
                  ) : null}
                </View>
              );
            })
          : null}
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: T.cyan700 },
  header: {
    backgroundColor: T.cyan700,
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 20,
    position: 'relative',
    overflow: 'hidden',
  },
  
  
  eyebrow: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 1.2,
    color: 'rgba(255,255,255,0.65)',
    marginBottom: 2,
  },
  title: { fontSize: 30, fontWeight: '800', color: T.white, lineHeight: 34 },
  subtitle: { marginTop: 4, fontSize: 12, color: 'rgba(255,255,255,0.78)' },
  scroll: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    
  },
   headerTitle: {
    fontSize: 30,
    fontWeight: '800',
    fontFamily: 'serif',
    color: T.white,
    letterSpacing: 0.2,
    lineHeight: 34,
  },
  content: { padding: 16, paddingBottom: 32, gap: 14 },
  summaryCard: {
    backgroundColor: T.white,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: T.slate200,
    padding: 18,
    shadowColor: T.slate900,
    shadowOpacity: 0.05,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 8,
    elevation: 2,
  },
  summaryHeaderRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  summaryMain: {
    flex: 1,
  },
  iconWrap: {
    width: 48,
    height: 48,
    borderRadius: 16,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  cardTitle: { fontSize: 16, fontWeight: '700', color: T.slate800, marginBottom: 6 },
  cardText: { fontSize: 13, lineHeight: 18, color: T.slate500 },
  summaryMetricsRow: {
    flexDirection: 'row',
    gap: 10,
    marginTop: 16,
  },
  summaryMetricCard: {
    flex: 1,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    paddingVertical: 14,
    paddingHorizontal: 10,
    alignItems: 'center',
  },
  summaryMetricValue: {
    fontSize: 22,
    fontWeight: '600',
    color: T.slate900,
    marginBottom: 4,
  },
  summaryMetricLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate500,
    textTransform: 'uppercase',
    textAlign: 'center',
  },
  summaryLoadingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginTop: 14,
  },
  inlineError: {
    backgroundColor: T.red100,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(239,68,68,0.2)',
    color: T.red700,
    padding: 12,
    fontSize: 12,
  },
  loadingBox: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    paddingVertical: 10,
  },
  loadingText: {
    fontSize: 12,
    color: T.slate600,
  },
  emptyCard: {
    backgroundColor: T.white,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: T.slate200,
    padding: 20,
    alignItems: 'center',
  },
  emptyIconWrap: {
    width: 52,
    height: 52,
    borderRadius: 18,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  emptyTitle: {
    fontSize: 17,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 6,
  },
  emptyText: {
    fontSize: 13,
    lineHeight: 19,
    color: T.slate500,
    textAlign: 'center',
  },
  dependentCard: {
    backgroundColor: T.white,
    borderRadius: 20,
    borderWidth: 1,
    borderColor: T.slate200,
    padding: 16,
    shadowColor: T.slate900,
    shadowOpacity: 0.05,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 8,
    elevation: 2,
  },
  dependentCardTop: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 10,
    marginBottom: 12,
  },
  

  circleTopRight: {
    position: 'absolute',
    top: -80,
    right: -80,
    width: 280,
    height: 280,
    borderRadius: 140,
    backgroundColor: 'rgba(255,255,255,0.08)',
  },
  circleBottomLeft: {
    position: 'absolute',
    bottom: -80,
    left: -60,
    width: 190,
    height: 190,
    borderRadius: 95,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  circleMidLeft: {
    position: 'absolute',
    top: 30,
    left: -90,
    width: 180,
    height: 180,
    borderRadius: 90,
    backgroundColor: 'rgba(255,255,255,0.05)',
  },



  dependentMain: {
    flex: 1,
  },
  badgeRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 12,
  },
  alertBadge: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 6,
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderWidth: 1,
    borderColor: 'rgba(6,182,212,0.2)',
    backgroundColor: 'rgba(6,182,212,0.08)',
  },
  alertBadgeWarning: {
    borderColor: 'rgba(245,158,11,0.18)',
    backgroundColor: T.amber100,
  },
  alertBadgeSuccess: {
    borderColor: 'rgba(34,197,94,0.2)',
    backgroundColor: T.green100,
  },
  alertBadgeText: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
  },
  alertBadgeTextWarning: {
    color: T.amber700,
  },
  alertBadgeTextSuccess: {
    color: T.green700,
  },
  nameRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    alignItems: 'center',
    gap: 8,
    marginBottom: 6,
  },
  dependentName: {
    fontSize: 18,
    fontWeight: '800',
    color: T.slate900,
    flexShrink: 1,
  },
  relationshipPill: {
    paddingHorizontal: 10,
    paddingVertical: 6,
    borderRadius: 999,
    backgroundColor: 'rgba(6,182,212,0.1)',
  },
  relationshipText: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
  },
  dependentSubtext: {
    fontSize: 12,
    color: T.slate500,
    lineHeight: 18,
  },
  statusPill: {
    borderWidth: 1,
    borderRadius: 999,
    paddingHorizontal: 10,
    paddingVertical: 6,
  },
  statusPillActive: {
    backgroundColor: T.green100,
    borderColor: 'rgba(34,197,94,0.25)',
  },
  statusPillPending: {
    backgroundColor: T.red100,
    borderColor: 'rgba(239,68,68,0.25)',
  },
  statusPillText: {
    fontSize: 11,
    fontWeight: '700',
  },
  statusPillTextActive: {
    color: T.green700,
  },
  statusPillTextPending: {
    color: T.red700,
  },
  careOverviewGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 14,
  },
  careOverviewCard: {
    width: '48%',
    borderRadius: 16,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    padding: 14,
  },
  careOverviewCardFull: {
    width: '100%',
  },
  carePrimaryText: {
    fontSize: 15,
    fontWeight: '700',
    color: T.slate900,
    marginBottom: 4,
  },
  careSecondaryText: {
    fontSize: 12,
    lineHeight: 18,
    color: T.slate600,
  },
  careMetaText: {
    marginTop: 10,
    fontSize: 11,
    lineHeight: 17,
    color: T.slate500,
  },
  queueOverviewMetrics: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginTop: 12,
    marginBottom: 2,
  },
  queueMetricCard: {
    width: '48%',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    padding: 10,
  },
  queueMetricLabel: {
    fontSize: 10,
    fontWeight: '700',
    color: T.slate500,
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  queueMetricText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.slate800,
  },
  infoGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
    marginBottom: 14,
  },
  infoItem: {
    width: '48%',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    padding: 12,
  },
  infoItemFull: {
    width: '100%',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    padding: 12,
  },
  infoLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate500,
    textTransform: 'uppercase',
    marginBottom: 4,
  },
  infoValue: {
    fontSize: 13,
    color: T.slate800,
    lineHeight: 18,
  },
  primaryButton: {
    borderRadius: 14,
    backgroundColor: T.cyan700,
    paddingVertical: 12,
    paddingHorizontal: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.white,
  },
  actionRow: {
    flexDirection: 'row',
    gap: 10,
    marginBottom: 10,
  },
  actionButton: {
    flex: 1,
  },
  secondaryButton: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.cyan700,
    backgroundColor: T.white,
    paddingVertical: 12,
    paddingHorizontal: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.cyan700,
  },
  ghostButton: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate300,
    backgroundColor: T.slate50,
    paddingVertical: 12,
    paddingHorizontal: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  ghostButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate700,
  },
  recordsSection: {
    marginTop: 14,
  },
  tabRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 12,
  },
  tabButton: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate300,
    backgroundColor: T.white,
    paddingHorizontal: 12,
    paddingVertical: 9,
  },
  tabButtonActive: {
    borderColor: T.cyan700,
    backgroundColor: T.cyan700,
  },
  tabButtonText: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate700,
  },
  tabButtonTextActive: {
    color: T.white,
  },
  recordsCard: {
    borderRadius: 18,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    overflow: 'hidden',
  },
  recordsHeader: {
    paddingHorizontal: 14,
    paddingTop: 14,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: T.slate200,
  },
  recordsTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 2,
  },
  recordsSubtitle: {
    fontSize: 11,
    color: T.slate500,
  },
  recordsBody: {
    padding: 14,
  },
  emptyRecordsText: {
    fontSize: 12,
    lineHeight: 18,
    color: T.slate500,
  },
  recordCard: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    padding: 14,
    marginBottom: 12,
  },
  recordTopRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 12,
  },
  recordMain: {
    flex: 1,
  },
  recordTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 3,
  },
  recordSubtitle: {
    fontSize: 11,
    lineHeight: 16,
    color: T.slate600,
    marginBottom: 2,
  },
  

 eyebrowRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginBottom: 4 },
  eyebrowDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: T.cyan500 },
  eyebrowText: { fontSize: 9, fontWeight: '700', letterSpacing: 0.9, textTransform: 'uppercase', color: T.cyan600 },




  recordMeta: {
    fontSize: 11,
    lineHeight: 16,
    color: T.slate500,
  },
  detailButton: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: T.slate50,
    borderWidth: 1,
    borderColor: T.slate200,
  },
  detailButtonText: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
  },
  detailPanel: {
    marginTop: 12,
    paddingTop: 12,
    borderTopWidth: 1,
    borderTopColor: T.slate200,
  },
  detailLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate600,
    textTransform: 'uppercase',
    letterSpacing: 0.3,
    marginBottom: 4,
    marginTop: 8,
  },
  detailValue: {
    fontSize: 12,
    lineHeight: 18,
    color: T.slate700,
  },
  medicineRow: {
    marginTop: 8,
    padding: 10,
    borderRadius: 12,
    backgroundColor: T.slate50,
    borderWidth: 1,
    borderColor: T.slate200,
  },
  medicineName: {
    fontSize: 12,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 3,
  },
  medicineMeta: {
    fontSize: 11,
    lineHeight: 16,
    color: T.slate600,
  },
  medicineInstructions: {
    fontSize: 11,
    lineHeight: 16,
    color: T.slate500,
    marginTop: 3,
  },
});
