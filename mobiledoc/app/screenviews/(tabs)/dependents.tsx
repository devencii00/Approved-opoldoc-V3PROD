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

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
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

function normalizeText(value: any): string {
  return typeof value === 'string' ? value.trim() : '';
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
  const [currentUser, setCurrentUser] = useState<CurrentUser | null>((globalThis as any)?.currentUser ?? null);
  const [dependents, setDependents] = useState<DependentUser[]>([]);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [expandedDependentId, setExpandedDependentId] = useState<number | null>(null);
  const [recordsByDependent, setRecordsByDependent] = useState<Record<number, DependentRecordsState>>({});

  useEffect(() => {
    let cancelled = false;

    async function loadDependents() {
      setLoading(true);
      setError('');

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
          setExpandedDependentId((current) => (current && mapped.some((item) => item.user_id === current) ? current : null));
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
  }, []);

  const dependentCountLabel = useMemo(() => {
    if (loading) return 'Loading linked dependents...';
    if (dependents.length === 0) return 'No linked dependent accounts found.';
    return `${dependents.length} linked dependent${dependents.length === 1 ? '' : 's'}`;
  }, [dependents.length, loading]);

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
        <Text style={styles.subtitle}>Review linked dependent accounts and their records from one place.</Text>
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={styles.content} showsVerticalScrollIndicator={false}>
        {error ? <Text style={styles.inlineError}>{error}</Text> : null}

        <View style={styles.summaryCard}>
          <View style={styles.iconWrap}>
            <Ionicons name="people-outline" size={24} color={T.cyan700} />
          </View>
          <Text style={styles.cardTitle}>Guardian access</Text>
          <Text style={styles.cardText}>{dependentCountLabel}</Text>
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
                      <Text style={styles.infoLabel}>Email</Text>
                      <Text style={styles.infoValue}>{normalizeText(dependent.email) || 'Not provided'}</Text>
                    </View>
                  </View>

                  <Pressable
                    onPress={() => handleToggleRecords(dependent.user_id)}
                    style={({ pressed }) => [styles.primaryButton, pressed && { opacity: 0.85 }]}
                  >
                    <Text style={styles.primaryButtonText}>{isExpanded ? 'Hide records' : 'View records'}</Text>
                  </Pressable>

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
  header: { backgroundColor: T.cyan700, paddingHorizontal: 20, paddingTop: 10, paddingBottom: 20 },
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
    marginTop: -10,
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
  iconWrap: {
    width: 48,
    height: 48,
    borderRadius: 16,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 12,
  },
  cardTitle: { fontSize: 16, fontWeight: '700', color: T.slate800, marginBottom: 6 },
  cardText: { fontSize: 13, lineHeight: 18, color: T.slate500 },
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
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.white,
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
