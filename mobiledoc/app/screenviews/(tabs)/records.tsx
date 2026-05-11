import React, { useEffect, useMemo, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import {
  ActivityIndicator,
  Animated,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleProp,
  StyleSheet,
  Text,
  View,
  ViewStyle,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  cyan100: '#cffafe',
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
  red700: '#b91c1c',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type RecordsTabKey = 'visits' | 'prescriptions' | 'vitals';

type AnimatedCardProps = {
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
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

function AnimatedCard({ children, delay = 0, style }: AnimatedCardProps) {
  const anim = useRef(new Animated.Value(0)).current;

  useEffect(() => {
    Animated.timing(anim, {
      toValue: 1,
      duration: 480,
      delay,
      useNativeDriver: true,
    }).start();
  }, [anim, delay]);

  return (
    <Animated.View
      style={[
        {
          opacity: anim,
          transform: [
            {
              translateY: anim.interpolate({
                inputRange: [0, 1],
                outputRange: [18, 0],
              }),
            },
          ],
        },
        style,
      ]}
    >
      {children}
    </Animated.View>
  );
}

function formatDateTime(value: any): string {
  if (!value) return 'Date unavailable';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Date unavailable';
  return `${date.toLocaleDateString()} · ${date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}`;
}

function formatDateOnly(value: any): string {
  if (!value) return 'Date unavailable';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Date unavailable';
  return date.toLocaleDateString();
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

function normalizeText(value: any): string {
  return typeof value === 'string' ? value.trim() : '';
}

function formatNumberLabel(value: any, suffix = ''): string {
  if (value == null || value === '') return '—';
  const numeric = Number(value);
  if (Number.isNaN(numeric)) return '—';
  return `${numeric.toFixed(1)}${suffix}`;
}

function computeBmi(heightCmRaw: any, weightKgRaw: any): { bmi: string; category: string } {
  const heightCm = Number(heightCmRaw);
  const weightKg = Number(weightKgRaw);
  if (Number.isNaN(heightCm) || Number.isNaN(weightKg) || heightCm <= 0 || weightKg <= 0) {
    return { bmi: '—', category: 'Unavailable' };
  }

  const heightM = heightCm / 100;
  const bmi = weightKg / (heightM * heightM);
  let category = 'Obese (30 and above)';
  if (bmi < 18.5) category = 'Underweight (Below 18.5)';
  else if (bmi < 25) category = 'Normal (18.5 - 24.9)';
  else if (bmi < 30) category = 'Overweight (25.0 - 29.9)';

  return { bmi: bmi.toFixed(1), category };
}

function TabCard({
  title,
  value,
  subtitle,
  icon,
  active,
  onPress,
  delay,
}: {
  title: string;
  value: string;
  subtitle: string;
  icon: React.ComponentProps<typeof Ionicons>['name'];
  active: boolean;
  onPress: () => void;
  delay: number;
}) {
  return (
    <AnimatedCard delay={delay} style={styles.tabCardWrap}>
      <Pressable
        onPress={onPress}
        style={({ pressed }) => [
          styles.tabCard,
          active && styles.tabCardActive,
          pressed && { opacity: 0.88 },
        ]}
      >
        <View style={[styles.tabIconCircle, active && styles.tabIconCircleActive]}>
          <Ionicons name={icon} size={18} color={active ? T.white : T.cyan700} />
        </View>
        <Text style={[styles.tabCardTitle, active && styles.tabCardTitleActive]}>{title}</Text>
        <Text style={[styles.tabCardValue, active && styles.tabCardValueActive]}>{value}</Text>
        <Text style={[styles.tabCardSubtitle, active && styles.tabCardSubtitleActive]}>{subtitle}</Text>
      </Pressable>
    </AnimatedCard>
  );
}

export default function PatientRecordsScreen() {
  const router = useRouter();
  const [activeTab, setActiveTab] = useState<RecordsTabKey>('visits');
  const [visits, setVisits] = useState<VisitHistoryItem[]>([]);
  const [prescriptions, setPrescriptions] = useState<PrescriptionHistoryItem[]>([]);
  const [vitals, setVitals] = useState<VitalHistoryItem[]>([]);
  const [expandedKey, setExpandedKey] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    let cancelled = false;

    async function loadRecords() {
      setLoading(true);
      setError('');

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          setError('Please log in again.');
          return;
        }

        const [visitsRes, prescriptionsRes, vitalsRes] = await Promise.all([
          fetch(`${API_BASE_URL}/visits?per_page=100`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/prescriptions?per_page=100`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/vitals?per_page=100`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
        ]);

        const [visitsData, prescriptionsData, vitalsData] = await Promise.all([
          visitsRes.json().catch(() => ({})),
          prescriptionsRes.json().catch(() => ({})),
          vitalsRes.json().catch(() => ({})),
        ]);

        if (!visitsRes.ok || !prescriptionsRes.ok || !vitalsRes.ok) {
          const anyMessage = visitsData?.message || prescriptionsData?.message || vitalsData?.message;
          if (!cancelled) {
            setError(typeof anyMessage === 'string' && anyMessage.length > 0 ? anyMessage : 'Unable to load records history.');
          }
          return;
        }

        const visitRows = Array.isArray(visitsData?.data) ? visitsData.data : [];
        const prescriptionRows = Array.isArray(prescriptionsData?.data) ? prescriptionsData.data : [];
        const vitalRows = Array.isArray(vitalsData?.data) ? vitalsData.data : [];

        const mappedVisits: VisitHistoryItem[] = visitRows.map((row: any) => {
          const doctor = row?.appointment?.doctor ? formatDoctorName(row.appointment.doctor) : (
            row?.prescriptions?.[0]?.doctor ? formatDoctorName(row.prescriptions[0].doctor) : 'Doctor'
          );
          const prescriptionSummaries = (Array.isArray(row?.prescriptions) ? row.prescriptions : [])
            .flatMap((prescription: any) => Array.isArray(prescription?.items) ? prescription.items : [])
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
        }).filter((item: VisitHistoryItem) => item.id.length > 0);

        const mappedPrescriptions: PrescriptionHistoryItem[] = prescriptionRows.map((row: any) => {
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
        }).filter((item: PrescriptionHistoryItem) => item.id.length > 0);

        const mappedVitals: VitalHistoryItem[] = vitalRows.map((row: any) => {
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
            bloodPressure: normalizeText(row?.blood_pressure) || '—',
            temperature: formatNumberLabel(row?.temperature, ' C'),
            pulseRate: row?.pulse_rate != null && row?.pulse_rate !== '' ? `${row.pulse_rate} bpm` : '—',
            bmi: bmi.bmi,
            bmiCategory: bmi.category,
          };
        }).filter((item: VitalHistoryItem) => item.id.length > 0);

        if (!cancelled) {
          setVisits(mappedVisits);
          setPrescriptions(mappedPrescriptions);
          setVitals(mappedVitals);
          setExpandedKey(null);
          setError('');
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    void loadRecords();
    return () => {
      cancelled = true;
    };
  }, []);

  const activeItemsCount = useMemo(() => {
    if (activeTab === 'visits') return visits.length;
    if (activeTab === 'prescriptions') return prescriptions.length;
    return vitals.length;
  }, [activeTab, prescriptions.length, visits.length, vitals.length]);

  function toggleDetails(key: string) {
    setExpandedKey((current) => current === key ? null : key);
  }

  function renderVisits() {
    if (visits.length === 0) {
      return <Text style={styles.emptyText}>No visit history found.</Text>;
    }

    return visits.map((item) => {
      const expanded = expandedKey === `visit-${item.id}`;
      return (
        <View key={item.id} style={styles.listCard}>
          <View style={styles.listCardTop}>
            <View style={styles.listMain}>
              <Text style={styles.listTitle}>{item.reason}</Text>
              <Text style={styles.listSubtitle}>{`${item.date} · ${item.doctor}`}</Text>
              <Text style={styles.listMeta}>{`${item.appointmentType} · Payment ${item.paymentStatus}`}</Text>
            </View>
            <Pressable onPress={() => toggleDetails(`visit-${item.id}`)} style={({ pressed }) => [styles.detailButton, pressed && { opacity: 0.85 }]}>
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

  function renderPrescriptions() {
    if (prescriptions.length === 0) {
      return <Text style={styles.emptyText}>No prescription history found.</Text>;
    }

    return prescriptions.map((item) => {
      const expanded = expandedKey === `prescription-${item.id}`;
      return (
        <View key={item.id} style={styles.listCard}>
          <View style={styles.listCardTop}>
            <View style={styles.listMain}>
              <Text style={styles.listTitle}>{item.summary}</Text>
              <Text style={styles.listSubtitle}>{`${item.date} · ${item.doctor}`}</Text>
              <Text style={styles.listMeta}>{item.reason}</Text>
            </View>
            <Pressable onPress={() => toggleDetails(`prescription-${item.id}`)} style={({ pressed }) => [styles.detailButton, pressed && { opacity: 0.85 }]}>
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
              {item.medicines.length > 0 ? item.medicines.map((medicine) => (
                <View key={medicine.id} style={styles.medicineRow}>
                  <Text style={styles.medicineName}>{medicine.name}</Text>
                  <Text style={styles.medicineMeta}>
                    {[medicine.dosage, medicine.frequency, medicine.duration].filter(Boolean).join(' · ') || 'No dosage details'}
                  </Text>
                  {medicine.instructions ? <Text style={styles.medicineInstructions}>{medicine.instructions}</Text> : null}
                </View>
              )) : <Text style={styles.detailValue}>No medicine items recorded.</Text>}
            </View>
          ) : null}
        </View>
      );
    });
  }

  function renderVitals() {
    if (vitals.length === 0) {
      return <Text style={styles.emptyText}>No vitals history found.</Text>;
    }

    return vitals.map((item) => {
      const expanded = expandedKey === `vital-${item.id}`;
      return (
        <View key={item.id} style={styles.listCard}>
          <View style={styles.listCardTop}>
            <View style={styles.listMain}>
              <Text style={styles.listTitle}>{item.recordedAt}</Text>
              <Text style={styles.listSubtitle}>{`${item.doctor} · BMI ${item.bmi}`}</Text>
              <Text style={styles.listMeta}>{`${item.bloodPressure} · ${item.temperature} · ${item.pulseRate}`}</Text>
            </View>
            <Pressable onPress={() => toggleDetails(`vital-${item.id}`)} style={({ pressed }) => [styles.detailButton, pressed && { opacity: 0.85 }]}>
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

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <ScrollView
        style={styles.pageScroll}
        contentContainerStyle={styles.pageScrollContent}
        showsVerticalScrollIndicator={false}
      >
        <View style={styles.header}>
          <View style={styles.circleTopRight} />
          <View style={styles.circleBottomLeft} />
          <View style={styles.circleMidLeft} />
          <View style={styles.headerRow}>
            <View style={{ flex: 1 }}>


 <View style={styles.eyebrowRow}>
              <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
              <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
            </View>

              <Text style={styles.headerTitle}>Records</Text>
              <Text style={styles.headerGreeting}>Review your visit history, prescriptions, and vitals.</Text>
            </View>
            <Pressable
              style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.85 }]}
               onPress={() => router.navigate('/screenviews' as any)}
            >
              <Text style={styles.headerBtnText}>Back</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.contentSurface}>
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}

          <View style={styles.tabsRow}>
            <TabCard
              title="Visits"
              value={String(visits.length)}
              subtitle="Consultation history"
              icon="calendar-outline"
              active={activeTab === 'visits'}
              onPress={() => setActiveTab('visits')}
              delay={20}
            />
            <TabCard
              title="Prescriptions"
              value={String(prescriptions.length)}
              subtitle="Medicines and notes"
              icon="medkit-outline"
              active={activeTab === 'prescriptions'}
              onPress={() => setActiveTab('prescriptions')}
              delay={40}
            />
            <TabCard
              title="Vitals"
              value={String(vitals.length)}
              subtitle="Recorded measurements"
              icon="pulse-outline"
              active={activeTab === 'vitals'}
              onPress={() => setActiveTab('vitals')}
              delay={60}
            />
          </View>

          <AnimatedCard delay={80} style={styles.sectionCard}>
            <View style={styles.sectionHeader}>
              <View style={{ flex: 1 }}>
                <Text style={styles.sectionTitle}>
                  {activeTab === 'visits' ? 'Visit history' : activeTab === 'prescriptions' ? 'Prescription history' : 'Vitals history'}
                </Text>
                <Text style={styles.sectionSubtitle}>
                  {loading
                    ? 'Loading history...'
                    : `${activeItemsCount} ${activeTab} entr${activeItemsCount === 1 ? 'y' : 'ies'} available`}
                </Text>
              </View>
              {loading ? <ActivityIndicator size="small" color={T.cyan700} /> : null}
            </View>
            <View style={styles.sectionBody}>
              {activeTab === 'visits' ? renderVisits() : null}
              {activeTab === 'prescriptions' ? renderPrescriptions() : null}
              {activeTab === 'vitals' ? renderVitals() : null}
            </View>
          </AnimatedCard>
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
  pageScroll: {
    flex: 1,
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  pageScrollContent: {
    flexGrow: 1,
  },
  header: {
    backgroundColor: T.cyan700,
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 30,
    position: 'relative',
    overflow: 'hidden',
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
  headerRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 12,
    position: 'relative',
    zIndex: 1,
  },
  headerEyebrow: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 1.2,
    color: 'rgba(255,255,255,0.65)',
    marginBottom: 2,
  },
  
 eyebrowRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginBottom: 4 },
  eyebrowDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: T.cyan500 },
  eyebrowText: { fontSize: 9, fontWeight: '700', letterSpacing: 0.9, textTransform: 'uppercase', color: T.cyan600 },
  headerTitle: {
    fontSize: 30,
    fontWeight: '800',
    fontFamily: 'serif',
    color: T.white,
    letterSpacing: 0.2,
    lineHeight: 34,
  },
  headerGreeting: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.75)',
    marginTop: 2,
    fontWeight: '400',
  },
  headerBtn: {
    marginTop: 4,
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  headerBtnText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.white,
  },
  contentSurface: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    paddingTop: 20,
    paddingHorizontal: 14,
    paddingBottom: 84,
  },
  inlineError: {
    fontSize: 12,
    color: T.red700,
    marginBottom: 10,
  },
  tabsRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 18,
  },
  tabCardWrap: {
    flex: 1,
  },
  tabCard: {
    minHeight: 144,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    padding: 12,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
  },
  tabCardActive: {
    backgroundColor: T.cyan700,
    borderColor: T.cyan700,
  },
  tabIconCircle: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  tabIconCircleActive: {
    backgroundColor: 'rgba(255,255,255,0.18)',
  },
  tabCardTitle: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate500,
    marginBottom: 4,
  },
  tabCardTitleActive: {
    color: 'rgba(255,255,255,0.8)',
  },
  tabCardValue: {
    fontSize: 20,
    fontWeight: '800',
    color: T.cyan700,
    marginBottom: 4,
  },
  tabCardValueActive: {
    color: T.white,
  },
  tabCardSubtitle: {
    fontSize: 10,
    lineHeight: 14,
    color: T.slate500,
  },
  tabCardSubtitleActive: {
    color: 'rgba(255,255,255,0.78)',
  },
  sectionCard: {
    backgroundColor: T.white,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
    overflow: 'hidden',
  },
  sectionHeader: {
    paddingHorizontal: 16,
    paddingTop: 14,
    paddingBottom: 10,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
  },
  sectionTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: T.slate800,
  },
  sectionSubtitle: {
    fontSize: 11,
    color: T.slate500,
    marginTop: 2,
  },
  sectionBody: {
    padding: 16,
  },
  emptyText: {
    fontSize: 12,
    color: T.slate500,
    lineHeight: 18,
  },
  listCard: {
    borderRadius: 16,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    padding: 14,
    marginBottom: 12,
  },
  listCardTop: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 12,
  },
  listMain: {
    flex: 1,
  },
  listTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 3,
  },
  listSubtitle: {
    fontSize: 11,
    color: T.slate600,
    lineHeight: 15,
    marginBottom: 2,
  },
  listMeta: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 15,
  },
  detailButton: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: T.white,
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
    backgroundColor: T.white,
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
