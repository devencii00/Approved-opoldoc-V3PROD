import React, { useCallback, useEffect, useMemo, useRef, useState } from 'react';
import type { ReactNode } from 'react';
import {
  ActivityIndicator,
  Animated,
  Modal,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleProp,
  StyleSheet,
  Text,
  TextInput,
  View,
  ViewStyle,
} from 'react-native';
import { useIsFocused } from '@react-navigation/native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  cyan400: '#22d3ee',
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
  green100: 'rgba(34,197,94,0.12)',
  green700: '#15803d',
  red700: '#b91c1c',
  amber100: 'rgba(245,158,11,0.12)',
  amber700: '#b45309',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');
const APP_BASE_URL = API_BASE_URL.replace(/\/api$/, '');
const WALK_IN_BLOCKED_SERVICE_CATEGORIES = ['obsterician - gynecologist', 'obstetrician - gynecologist', 'general surgeon'] as const;
const DAY_KEYS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as const;

type DayKey = typeof DAY_KEYS[number];

type ServiceOption = {
  id: string;
  name: string;
  category: string;
  description: string;
  price: number | null;
  durationMinutes: number | null;
};

type DoctorScheduleOption = {
  id: string;
  dayKey: DayKey;
  startTime: string;
  endTime: string;
  isAvailable: boolean;
};

type DoctorOption = {
  id: string;
  name: string;
  specialization: string;
  scheduleDayKeys: DayKey[];
  hasAnySchedule: boolean;
  scheduleSlots: DoctorScheduleOption[];
};

type QueueStatus = {
  queueId: string;
  queueNumber: string;
  status: 'waiting' | 'serving' | 'done' | 'cancelled';
  doctor: string;
  doctorId: string;
  position: number | null;
  estimatedWaitMinutes: number | null;
  services: ServiceOption[];
  totalFee: number;
  servingQueueNumber: string | null;
  nextQueueNumber: string | null;
};

type DropdownOption = {
  id: string;
  label: string;
  description?: string;
  meta?: string;
  disabled?: boolean;
};

type AnimatedCardProps = {
  children: ReactNode;
  delay?: number;
  style?: StyleProp<ViewStyle>;
};

type SectionCardProps = {
  title: string;
  subtitle?: string;
  badge?: string;
  delay?: number;
  style?: StyleProp<ViewStyle>;
  collapsed?: boolean;
  summary?: ReactNode;
  onToggleCollapse?: () => void;
  children: ReactNode;
};

type SelectionSheetProps = {
  visible: boolean;
  title: string;
  subtitle: string;
  options: DropdownOption[];
  multiSelect?: boolean;
  selectedIds: string[];
  onClose: () => void;
  onSelect: (id: string) => void;
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

function SectionCard({
  title,
  subtitle,
  badge,
  delay = 0,
  style,
  collapsed = false,
  summary,
  onToggleCollapse,
  children,
}: SectionCardProps) {
  return (
    <AnimatedCard delay={delay} style={[styles.sectionCard, style]}>
      <View style={styles.sectionHeader}>
        <View style={styles.sectionHeaderMain}>
          {badge ? (
            <View style={styles.sectionBadge}>
              <Text style={styles.sectionBadgeText}>{badge}</Text>
            </View>
          ) : null}
          <View style={{ flex: 1 }}>
            <Text style={styles.sectionTitle}>{title}</Text>
            {subtitle ? <Text style={styles.sectionSubtitle}>{subtitle}</Text> : null}
            {summary ? <View style={styles.sectionSummaryWrap}>{summary}</View> : null}
          </View>
        </View>
        {onToggleCollapse ? (
          <Pressable
            onPress={onToggleCollapse}
            style={({ pressed }) => [styles.sectionToggleButton, pressed && { opacity: 0.8 }]}
          >
            <Ionicons name={collapsed ? 'chevron-down' : 'chevron-up'} size={18} color={T.slate600} />
          </Pressable>
        ) : null}
      </View>
      {!collapsed ? <View style={styles.sectionBody}>{children}</View> : null}
    </AnimatedCard>
  );
}

function SelectionSheet({
  visible,
  title,
  subtitle,
  options,
  multiSelect = false,
  selectedIds,
  onClose,
  onSelect,
}: SelectionSheetProps) {
  return (
    <Modal visible={visible} transparent animationType="fade" onRequestClose={onClose}>
      <Pressable style={styles.modalBackdrop} onPress={onClose}>
        <View />
      </Pressable>
      <View style={styles.sheet}>
        <View style={styles.sheetHeader}>
          <View style={{ flex: 1 }}>
            <Text style={styles.sheetTitle}>{title}</Text>
            <Text style={styles.sheetSubtitle}>{subtitle}</Text>
          </View>
          <Pressable style={({ pressed }) => [styles.sheetClose, pressed && { opacity: 0.75 }]} onPress={onClose}>
            <Ionicons name="close" size={18} color={T.slate700} />
          </Pressable>
        </View>

        <ScrollView style={styles.sheetScroll} contentContainerStyle={styles.sheetScrollContent}>
          {options.map((option) => {
            const selected = selectedIds.includes(option.id);
            return (
              <Pressable
                key={option.id}
                disabled={option.disabled}
                onPress={() => onSelect(option.id)}
                style={({ pressed }) => [
                  styles.sheetOption,
                  selected && styles.sheetOptionActive,
                  option.disabled && styles.sheetOptionDisabled,
                  pressed && !option.disabled && { opacity: 0.85 },
                ]}
              >
                <View style={{ flex: 1 }}>
                  <Text
                    style={[
                      styles.sheetOptionLabel,
                      selected && styles.sheetOptionLabelActive,
                      option.disabled && styles.sheetOptionLabelDisabled,
                    ]}
                  >
                    {option.label}
                  </Text>
                  {option.description ? <Text style={styles.sheetOptionDescription}>{option.description}</Text> : null}
                  {option.meta ? <Text style={styles.sheetOptionMeta}>{option.meta}</Text> : null}
                </View>
                <Ionicons
                  name={selected ? (multiSelect ? 'checkbox' : 'radio-button-on') : multiSelect ? 'square-outline' : 'radio-button-off'}
                  size={20}
                  color={selected ? T.cyan700 : T.slate400}
                />
              </Pressable>
            );
          })}
        </ScrollView>
      </View>
    </Modal>
  );
}

function normalizeCategory(value: string): string {
  const normalized = value.trim().toLowerCase().replace(/\s+/g, ' ');
  if (normalized === 'obsterician - gynecologist') return 'obstetrician - gynecologist';
  return normalized;
}

function extractServiceCategory(serviceName: string): string {
  const [rawCategory] = serviceName.split(':', 1);
  return normalizeCategory(rawCategory ?? serviceName);
}

function normalizeCompareValue(value: string): string {
  return value.trim().toLowerCase().replace(/\s+/g, ' ');
}

function getServiceDescription(service: ServiceOption): string {
  const description = typeof service.description === 'string' ? service.description.trim() : '';
  if (!description) return 'No description provided.';
  if (normalizeCompareValue(description) === normalizeCompareValue(service.name)) return 'No description provided.';
  return description;
}

function formatDoctorName(raw: any): string {
  const first = raw?.firstname ? String(raw.firstname) : '';
  const last = raw?.lastname ? String(raw.lastname) : '';
  const full = `Dr. ${[first, last].filter(Boolean).join(' ')}`.trim();
  return full === 'Dr.' ? 'Doctor' : full;
}

function formatPriceLabel(value: number | null): string {
  if (typeof value !== 'number' || Number.isNaN(value)) return 'Price unavailable';
  return `P ${value.toFixed(2)}`;
}

function formatDurationLabel(value: number | null): string {
  if (typeof value !== 'number' || Number.isNaN(value) || value <= 0) return 'Duration unavailable';
  return `${value} mins`;
}

function formatCurrency(value: number): string {
  if (Number.isNaN(value)) return 'P 0.00';
  return `P ${value.toFixed(2)}`;
}

function minutesFromTime(value: string): number {
  const parts = String(value).split(':');
  return (Number(parts[0] ?? 0) * 60) + Number(parts[1] ?? 0);
}

function getCurrentDayKey(): DayKey {
  return DAY_KEYS[new Date().getDay()];
}

function isDoctorAvailableForWalkIn(doctor: DoctorOption): boolean {
  const currentDayKey = getCurrentDayKey();
  const now = new Date();
  const nowMinutes = now.getHours() * 60 + now.getMinutes();

  return doctor.scheduleSlots.some((slot) => (
    slot.dayKey === currentDayKey &&
    slot.isAvailable &&
    minutesFromTime(slot.startTime) <= nowMinutes &&
    minutesFromTime(slot.endTime) >= nowMinutes
  ));
}

export default function PatientQueueScreen() {
  const router = useRouter();
  const isFocused = useIsFocused();
  const loadingQueueStatusRef = useRef(false);
  const loadingQueueLineRef = useRef(false);

  const [services, setServices] = useState<ServiceOption[]>([]);
  const [doctors, setDoctors] = useState<DoctorOption[]>([]);

  const [selectedServiceIds, setSelectedServiceIds] = useState<string[]>([]);
  const [selectedDoctorId, setSelectedDoctorId] = useState('');
  const [reason, setReason] = useState('');

  const [stepOneCollapsed, setStepOneCollapsed] = useState(false);
  const [serviceSheetOpen, setServiceSheetOpen] = useState(false);
  const [doctorSheetOpen, setDoctorSheetOpen] = useState(false);

  const [queueStatus, setQueueStatus] = useState<QueueStatus | null>(null);
  const [queueDetailsExpanded, setQueueDetailsExpanded] = useState(false);
  const [loading, setLoading] = useState(false);
  const [joining, setJoining] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [needsMedicalBackground, setNeedsMedicalBackground] = useState(false);

  const selectedServices = useMemo(
    () => services.filter((service) => selectedServiceIds.includes(service.id)),
    [selectedServiceIds, services]
  );
  const selectedServicesTotalFee = useMemo(
    () => selectedServices.reduce((sum, service) => sum + (typeof service.price === 'number' ? service.price : 0), 0),
    [selectedServices]
  );
  const selectedCategory = selectedServices[0]?.category ?? '';

  const filteredDoctors = useMemo(() => {
    if (!selectedCategory) return [];
    return doctors.filter((doctor) => {
      const specialization = normalizeCategory(doctor.specialization);
      return specialization.includes(selectedCategory) || selectedCategory.includes(specialization);
    });
  }, [doctors, selectedCategory]);

  const selectedDoctor = filteredDoctors.find((doctor) => doctor.id === selectedDoctorId) ?? null;
  const canJoinQueue = !loading && !joining && !queueStatus && selectedServiceIds.length > 0 && selectedDoctorId.length > 0;

  const stepOneSummary = useMemo(() => {
    if (!selectedDoctor || selectedServices.length === 0) return null;

    return (
      <View style={styles.summaryStack}>
        <Text style={styles.summaryText}>{selectedDoctor.name}</Text>
        {selectedServices.slice(0, 2).map((service) => (
          <Text key={service.id} style={styles.summaryText}>
            {`${service.name} • ${formatPriceLabel(service.price)} • ${formatDurationLabel(service.durationMinutes)}`}
          </Text>
        ))}
        {selectedServices.length > 2 ? (
          <Text style={styles.summaryText}>{`+${selectedServices.length - 2} more service${selectedServices.length - 2 > 1 ? 's' : ''}`}</Text>
        ) : null}
      </View>
    );
  }, [selectedDoctor, selectedServices]);

  const serviceOptions: DropdownOption[] = services.map((service) => {
    const categoryMismatch = selectedCategory.length > 0 && service.category !== selectedCategory;
    return {
      id: service.id,
      label: service.name,
      description: getServiceDescription(service),
      meta: `${service.category} • ${formatPriceLabel(service.price)} • ${formatDurationLabel(service.durationMinutes)}`,
      disabled: categoryMismatch,
    };
  });

  const doctorOptions: DropdownOption[] = filteredDoctors.map((doctor) => {
    const availableNow = isDoctorAvailableForWalkIn(doctor);
    return {
      id: doctor.id,
      label: doctor.name,
      description: availableNow
        ? doctor.specialization
        : `${doctor.specialization} • No active schedule right now`,
      meta: availableNow ? 'Available for walk-in now' : 'Choose another doctor or try later',
      disabled: !availableNow,
    };
  });

  const loadQueueStatus = useCallback(async (token: string) => {
    if (loadingQueueStatusRef.current) return;

    loadingQueueStatusRef.current = true;
    try {
      const response = await fetch(`${API_BASE_URL}/queues?per_page=10`, {
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        setQueueStatus(null);
        return;
      }

      const queueRaw = Array.isArray(data?.data) ? data.data : [];
      const activeQueue = queueRaw.find((q: any) => q?.status === 'waiting' || q?.status === 'serving') ?? null;
      const mappedQueue: QueueStatus | null = activeQueue
        ? {
            queueId: String(activeQueue.queue_id ?? ''),
            queueNumber: activeQueue.queue_number != null ? String(activeQueue.queue_number) : '',
            status: activeQueue.status === 'serving' ? 'serving' : 'waiting',
            doctorId: activeQueue?.appointment?.doctor_id != null ? String(activeQueue.appointment.doctor_id) : '',
            position: typeof activeQueue.position === 'number' ? activeQueue.position : null,
            estimatedWaitMinutes: typeof activeQueue.estimated_wait_minutes === 'number' ? activeQueue.estimated_wait_minutes : null,
            services: Array.isArray(activeQueue?.appointment?.services)
              ? activeQueue.appointment.services
                  .map((service: any) => {
                    const serviceName = typeof service?.service_name === 'string' ? service.service_name : '';
                    const category = extractServiceCategory(serviceName);
                    return {
                      id: String(service?.service_id ?? ''),
                      name: serviceName,
                      category,
                      description: typeof service?.description === 'string' ? service.description : '',
                      price: typeof service?.price === 'number' ? service.price : service?.price != null ? Number(service.price) : null,
                      durationMinutes: typeof service?.duration_minutes === 'number'
                        ? service.duration_minutes
                        : service?.duration_minutes != null
                          ? Number(service.duration_minutes)
                          : null,
                    };
                  })
                  .filter((service: ServiceOption) => service.id.length > 0)
              : [],
            totalFee: Array.isArray(activeQueue?.appointment?.services)
              ? activeQueue.appointment.services.reduce((sum: number, service: any) => {
                  const price = typeof service?.price === 'number' ? service.price : service?.price != null ? Number(service.price) : 0;
                  return sum + (Number.isNaN(price) ? 0 : price);
                }, 0)
              : 0,
            servingQueueNumber: null,
            nextQueueNumber: null,
            doctor: (() => {
              const doctorFirst = activeQueue?.appointment?.doctor?.firstname ? String(activeQueue.appointment.doctor.firstname) : '';
              const doctorLast = activeQueue?.appointment?.doctor?.lastname ? String(activeQueue.appointment.doctor.lastname) : '';
              const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
              return doctorName === 'Dr.' ? 'Doctor' : doctorName;
            })(),
          }
        : null;

      setQueueStatus(mappedQueue);
    } catch {
      setQueueStatus(null);
    } finally {
      loadingQueueStatusRef.current = false;
    }
  }, []);

  const loadQueueLine = useCallback(async (doctorId: string, queueId: string) => {
    if (!doctorId || !queueId || loadingQueueLineRef.current) return;

    loadingQueueLineRef.current = true;
    try {
      const response = await fetch(`${APP_BASE_URL}/queue-display/data?doctor_id=${encodeURIComponent(doctorId)}`, {
        headers: { Accept: 'application/json' },
      });
      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        return;
      }

      const nowServing = Array.isArray(data?.now_serving) ? data.now_serving[0] : null;
      const nextItem = Array.isArray(data?.next) ? data.next[0] : null;

      setQueueStatus((current) => {
        if (!current || current.queueId !== queueId) return current;
        return {
          ...current,
          servingQueueNumber: nowServing?.queue_number != null ? String(nowServing.queue_number) : null,
          nextQueueNumber: nextItem?.queue_number != null ? String(nextItem.queue_number) : null,
        };
      });
    } catch {
      // Ignore display board fetch errors and keep the queue card usable.
    } finally {
      loadingQueueLineRef.current = false;
    }
  }, []);

  useEffect(() => {
    if (!isFocused) return;

    let cancelled = false;
    let intervalId: ReturnType<typeof setInterval> | undefined;

    async function loadInitialData() {
      setLoading(true);
      setError('');
      setSuccess('');

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          router.replace('/screenviews/aut-landing/login-screen' as any);
          return;
        }

        const [servicesRes, doctorsRes] = await Promise.all([
          fetch(`${API_BASE_URL}/services?per_page=100`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/doctors?per_page=100&available_only=1`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
        ]);

        const [servicesData, doctorsData] = await Promise.all([
          servicesRes.json().catch(() => ({})),
          doctorsRes.json().catch(() => ({})),
        ]);

        if (!servicesRes.ok || !doctorsRes.ok) {
          const anyMessage = servicesData?.message || doctorsData?.message;
          if (!cancelled) {
            setError(typeof anyMessage === 'string' && anyMessage.length > 0 ? anyMessage : 'Unable to load walk-in options.');
          }
          return;
        }

        const rawServices = Array.isArray(servicesData?.data) ? servicesData.data : [];
        const rawDoctors = Array.isArray(doctorsData?.data) ? doctorsData.data : [];

        const mappedServices: ServiceOption[] = rawServices
          .map((service: any) => {
            const serviceName = typeof service?.service_name === 'string' ? service.service_name : '';
            const category = extractServiceCategory(serviceName);
            return {
              id: String(service?.service_id ?? ''),
              name: serviceName,
              category,
              description: typeof service?.description === 'string' ? service.description : '',
              price: typeof service?.price === 'number' ? service.price : service?.price != null ? Number(service.price) : null,
              durationMinutes: typeof service?.duration_minutes === 'number'
                ? service.duration_minutes
                : service?.duration_minutes != null
                  ? Number(service.duration_minutes)
                  : null,
            };
          })
          .filter((service: ServiceOption) => (
            service.id.length > 0 &&
            !WALK_IN_BLOCKED_SERVICE_CATEGORIES.includes(service.category as (typeof WALK_IN_BLOCKED_SERVICE_CATEGORIES)[number])
          ));

        const mappedDoctors: DoctorOption[] = rawDoctors
          .map((doctor: any) => {
            const rawSchedules = Array.isArray(doctor?.doctor_schedules)
              ? doctor.doctor_schedules
              : Array.isArray(doctor?.doctorSchedules)
                ? doctor.doctorSchedules
                : [];

            const scheduleSlots: DoctorScheduleOption[] = rawSchedules
              .map((slot: any) => ({
                id: String(slot?.schedule_id ?? ''),
                dayKey: (typeof slot?.day_of_week === 'string' ? slot.day_of_week : 'mon') as DayKey,
                startTime: typeof slot?.start_time === 'string' ? slot.start_time : '00:00:00',
                endTime: typeof slot?.end_time === 'string' ? slot.end_time : '00:00:00',
                isAvailable: Boolean(slot?.is_available),
              }))
              .filter((slot: DoctorScheduleOption) => slot.id.length > 0);

            const scheduleDayKeys = Array.from(
              new Set(
                scheduleSlots
                  .filter((slot) => slot.isAvailable)
                  .map((slot) => slot.dayKey)
                  .filter((day) => DAY_KEYS.includes(day))
              )
            ) as DayKey[];

            return {
              id: String(doctor?.user_id ?? ''),
              name: formatDoctorName(doctor),
              specialization: typeof doctor?.specialization === 'string' ? doctor.specialization : 'Doctor',
              scheduleDayKeys,
              hasAnySchedule: scheduleDayKeys.length > 0,
              scheduleSlots,
            };
          })
          .filter((doctor: DoctorOption) => doctor.id.length > 0);

        if (!cancelled) {
          setServices(mappedServices);
          setDoctors(mappedDoctors);
        }

        await loadQueueStatus(token);
        intervalId = setInterval(() => {
          void loadQueueStatus(token);
        }, 15000);
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    void loadInitialData();
    return () => {
      cancelled = true;
      if (intervalId) clearInterval(intervalId);
    };
  }, [isFocused, loadQueueStatus, router]);

  useEffect(() => {
    if (!isFocused) return;

    const doctorId = queueStatus?.doctorId ?? '';
    const queueId = queueStatus?.queueId ?? '';

    if (!doctorId || !queueId) {
      return;
    }

    let cancelled = false;
    let intervalId: ReturnType<typeof setInterval> | undefined;

    async function syncQueueLine() {
      if (cancelled) return;
      await loadQueueLine(doctorId, queueId);
    }

    void syncQueueLine();
    intervalId = setInterval(() => {
      void syncQueueLine();
    }, 10000);

    return () => {
      cancelled = true;
      if (intervalId) clearInterval(intervalId);
    };
  }, [isFocused, loadQueueLine, queueStatus?.doctorId, queueStatus?.queueId]);

  function toggleService(serviceId: string) {
    const service = services.find((item) => item.id === serviceId);
    if (!service) return;

    setError('');
    setSuccess('');

    const alreadySelected = selectedServiceIds.includes(serviceId);
    if (alreadySelected) {
      const nextIds = selectedServiceIds.filter((id) => id !== serviceId);
      const nextServices = services.filter((item) => nextIds.includes(item.id));
      const nextCategory = nextServices[0]?.category ?? '';
      setSelectedServiceIds(nextIds);
      if (nextCategory !== selectedCategory) {
        setSelectedDoctorId('');
        setStepOneCollapsed(false);
      }
      return;
    }

    if (selectedCategory && selectedCategory !== service.category) {
      setError('All selected services must stay under the same specialization.');
      return;
    }

    setSelectedServiceIds((current) => [...current, serviceId]);
    setStepOneCollapsed(false);
  }

  function chooseDoctor(doctorId: string) {
    setSelectedDoctorId(doctorId);
    setDoctorSheetOpen(false);
    setError('');
    setSuccess('');
    setStepOneCollapsed(true);
  }

  async function handleJoinQueue() {
    setError('');
    setSuccess('');
    setNeedsMedicalBackground(false);

    if (selectedServiceIds.length === 0) {
      setError('Please choose at least one service.');
      return;
    }
    if (!selectedDoctorId) {
      setError('Please choose a doctor.');
      return;
    }
    if (queueStatus) {
      setError('You already have an active queue entry.');
      return;
    }

    setJoining(true);
    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        router.replace('/screenviews/aut-landing/login-screen' as any);
        return;
      }

      const response = await fetch(`${API_BASE_URL}/queues/join`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          doctor_id: Number(selectedDoctorId),
          reason_for_visit: reason.trim().length > 0 ? reason.trim() : null,
          service_ids: selectedServiceIds.map((id) => Number(id)),
        }),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        if (response.status === 428 || data?.code === 'MEDICAL_BACKGROUND_REQUIRED') {
          setNeedsMedicalBackground(true);
        }
        const message = typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to join the queue.';
        setError(message);
        return;
      }

      const appointment = data?.appointment ?? null;
      const doctorFirst = appointment?.doctor?.firstname ? String(appointment.doctor.firstname) : '';
      const doctorLast = appointment?.doctor?.lastname ? String(appointment.doctor.lastname) : '';
      const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();

      setQueueStatus({
        queueId: String(data?.queue_id ?? ''),
        queueNumber: data?.queue_number != null ? String(data.queue_number) : '',
        status: data?.status === 'serving' ? 'serving' : 'waiting',
        doctorId: appointment?.doctor_id != null ? String(appointment.doctor_id) : selectedDoctorId,
        doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
        position: typeof data?.position === 'number' ? data.position : null,
        estimatedWaitMinutes: typeof data?.estimated_wait_minutes === 'number' ? data.estimated_wait_minutes : null,
        services: selectedServices,
        totalFee: selectedServicesTotalFee,
        servingQueueNumber: null,
        nextQueueNumber: null,
      });
      setQueueDetailsExpanded(false);
      setSelectedServiceIds([]);
      setSelectedDoctorId('');
      setReason('');
      setStepOneCollapsed(false);
      setSuccess('You have joined the queue with status waiting.');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setJoining(false);
    }
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <ScrollView
        style={styles.pageScroll}
        contentContainerStyle={styles.pageScrollContent}
        showsVerticalScrollIndicator={false}
        keyboardShouldPersistTaps="handled"
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

              <Text style={styles.headerTitle}>Join Queue</Text>
              <Text style={styles.headerGreeting}>Choose your service & doctor.</Text>
            </View>
            <Pressable
              style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.85 }]}
              onPress={() => router.replace('/screenviews/(tabs)' as any)}
            >
              <Text style={styles.headerBtnText}>Back</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.contentSurface}>
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}
          {success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}
          {needsMedicalBackground ? (
            <Pressable
              onPress={() => router.push('/screenviews/medical-bg' as any)}
              style={({ pressed }) => [styles.warningButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.warningButtonText}>Add medical background now</Text>
            </Pressable>
          ) : null}

          <View style={styles.infoRow}>
            <AnimatedCard delay={60} style={styles.infoCard}>
              <View style={styles.infoCardInner}>
                <View style={styles.infoCardTopRow}>
                  <View style={styles.infoIconCircle}>
                    {loading ? <ActivityIndicator size="small" color={T.cyan700} /> : <Ionicons name="people-outline" size={18} color={T.cyan700} />}
                  </View>
                  <View style={styles.infoCardTopCopy}>
                    <Text style={styles.infoLabel}>Queue line</Text>
                    <Text style={styles.queueHeadline}>{queueStatus ? `#${queueStatus.queueNumber || '—'}` : 'Not in the queue'}</Text>
                  </View>
                </View>

                <ScrollView
                  style={styles.infoCardScroll}
                  contentContainerStyle={styles.infoCardScrollContent}
                  showsVerticalScrollIndicator={false}
                  nestedScrollEnabled
                >
                  {queueStatus ? (
                    <>
                      <View style={styles.queueMetricRow}>
                        <Text style={styles.queueMetricLabel}>Serving queue</Text>
                        <Text style={styles.queueMetricValue}>
                          {queueStatus.servingQueueNumber ? `#${queueStatus.servingQueueNumber}` : 'No active serving queue'}
                        </Text>
                      </View>
                      <View style={styles.queueMetricRow}>
                        <Text style={styles.queueMetricLabel}>Next in line</Text>
                        <Text style={styles.queueMetricValue}>
                          {queueStatus.nextQueueNumber ? `#${queueStatus.nextQueueNumber}` : 'Waiting for line update'}
                        </Text>
                      </View>
                      <View style={styles.queueMetricRow}>
                        <Text style={styles.queueMetricLabel}>Your position</Text>
                        <Text style={styles.queueMetricValue}>
                          {queueStatus.position != null ? String(queueStatus.position) : 'Updating'}
                        </Text>
                      </View>
                      <View style={styles.queueMetricRow}>
                        <Text style={styles.queueMetricLabel}>Status</Text>
                        <Text style={styles.queueMetricValue}>{queueStatus.status === 'serving' ? 'Serving' : 'Waiting'}</Text>
                      </View>
                      <Text style={styles.infoSub}>
                        {`${queueStatus.doctor}${queueStatus.estimatedWaitMinutes != null ? ` · Est. wait ${queueStatus.estimatedWaitMinutes} mins` : ''}`}
                      </Text>

                      <Pressable
                        onPress={() => setQueueDetailsExpanded((current) => !current)}
                        style={({ pressed }) => [styles.queueDetailsToggle, pressed && { opacity: 0.85 }]}
                      >
                        <Text style={styles.queueDetailsToggleText}>Services & total fee</Text>
                        <Ionicons
                          name={queueDetailsExpanded ? 'chevron-up' : 'chevron-down'}
                          size={16}
                          color={T.cyan700}
                        />
                      </Pressable>

                      {queueDetailsExpanded ? (
                        <View style={styles.queueDetailsPanel}>
                          {queueStatus.services.map((service) => (
                            <View key={service.id} style={styles.queueServiceRow}>
                              <View style={{ flex: 1 }}>
                                <Text style={styles.queueServiceTitle}>{service.name}</Text>
                                <Text style={styles.queueServiceMeta}>{formatDurationLabel(service.durationMinutes)}</Text>
                              </View>
                              <Text style={styles.queueServicePrice}>{formatPriceLabel(service.price)}</Text>
                            </View>
                          ))}
                          <View style={styles.queueTotalRow}>
                            <Text style={styles.queueTotalLabel}>Total fee</Text>
                            <Text style={styles.queueTotalValue}>{formatCurrency(queueStatus.totalFee)}</Text>
                          </View>
                        </View>
                      ) : null}
                    </>
                  ) : (
                    <Text style={styles.infoSub}>
                      Join the walk-in queue to see your position.
                    </Text>
                  )}
                </ScrollView>
              </View>
            </AnimatedCard>
          </View>

          <SectionCard
            title="Walk-in Details"
            subtitle="Mobile walk-ins are created immediately and placed in waiting status once the selected doctor is on active schedule."
            badge="Step 1"
            delay={80}
            collapsed={stepOneCollapsed}
            summary={stepOneSummary}
            onToggleCollapse={() => setStepOneCollapsed((current) => !current)}
          >
            <Text style={styles.label}>Appointment type</Text>
            <View style={styles.staticValueRow}>
              <View style={styles.staticPill}>
                <Text style={styles.staticPillText}>Walk-in</Text>
              </View>
            </View>

            <Text style={[styles.label, { marginTop: 14 }]}>Service</Text>
            <Pressable
              onPress={() => setServiceSheetOpen(true)}
              style={({ pressed }) => [styles.dropdownButton, pressed && { opacity: 0.85 }]}
            >
              <View style={{ flex: 1 }}>
                <Text style={styles.dropdownValue}>
                  {selectedServices.length > 0 ? selectedServices.map((service) => service.name).join(', ') : 'Select one or more services'}
                </Text>
                <Text style={styles.dropdownHint}>Eligible walk-in services show their description, fee, and duration before you choose.</Text>
              </View>
              <Ionicons name="chevron-down" size={18} color={T.slate500} />
            </Pressable>

            {selectedServices.length > 0 ? (
              <View style={styles.selectedServicesSection}>
                <Text style={styles.selectedServicesTotal}>{`Current selection total: ${formatCurrency(selectedServicesTotalFee)}`}</Text>
                {selectedServices.length > 1 ? <Text style={styles.slideHint}>Slide to see more</Text> : null}
                <ScrollView horizontal showsHorizontalScrollIndicator={false} contentContainerStyle={styles.selectedServicesRow}>
                  {selectedServices.map((service) => (
                    <View key={service.id} style={styles.selectedServiceCard}>
                      <Text style={styles.selectedServiceTitle}>{service.name}</Text>
                      <Text style={styles.selectedServiceDescription}>{getServiceDescription(service)}</Text>
                      <Text style={styles.selectedServiceMeta}>
                        {formatPriceLabel(service.price)} · {formatDurationLabel(service.durationMinutes)}
                      </Text>
                    </View>
                  ))}
                </ScrollView>
              </View>
            ) : null}

            <Text style={[styles.label, { marginTop: 14 }]}>Doctor</Text>
            <Pressable
              disabled={!selectedCategory}
              onPress={() => setDoctorSheetOpen(true)}
              style={({ pressed }) => [
                styles.dropdownButton,
                !selectedCategory && styles.dropdownButtonDisabled,
                pressed && selectedCategory && { opacity: 0.85 },
              ]}
            >
              <View style={{ flex: 1 }}>
                <Text style={[styles.dropdownValue, !selectedCategory && styles.dropdownValueMuted]}>
                  {selectedDoctor?.name ?? 'Choose service first'}
                </Text>
                <Text style={styles.dropdownHint}>
                  {selectedCategory
                    ? 'Doctors are filtered by specialization and current active schedule.'
                    : 'Select at least one service to unlock doctors.'}
                </Text>
              </View>
              <Ionicons name="chevron-down" size={18} color={selectedCategory ? T.slate500 : T.slate300} />
            </Pressable>

            {selectedCategory && doctorOptions.length === 0 ? (
              <Text style={[styles.helperText, { marginTop: 10 }]}>No doctors match the selected service right now.</Text>
            ) : null}
          </SectionCard>

          <SectionCard
            title="Reason"
            subtitle="Optional notes help the clinic understand your concern before you join the queue."
            badge="Step 2"
            delay={120}
            style={{ marginBottom: 20 }}
          >
            {queueStatus ? (
              <View style={styles.statusCard}>
                <Text style={styles.statusTitle}>You are already in the queue.</Text>
                <Text style={styles.statusSub}>
                  {`${queueStatus.doctor} · ${queueStatus.status === 'serving' ? 'Now serving' : 'Waiting'}${queueStatus.position != null ? ` · Position ${queueStatus.position}` : ''}`}
                </Text>
                {queueStatus.estimatedWaitMinutes != null ? (
                  <Text style={styles.statusSub}>{`Estimated wait: ${queueStatus.estimatedWaitMinutes} mins`}</Text>
                ) : null}
              </View>
            ) : null}

            <TextInput
              value={reason}
              onChangeText={setReason}
              placeholder="Add a reason for joining the queue"
              placeholderTextColor={T.slate400}
              multiline
              textAlignVertical="top"
              scrollEnabled
              style={styles.reasonInput}
            />

            <Pressable
              disabled={!canJoinQueue}
              onPress={handleJoinQueue}
              style={({ pressed }) => [
                styles.primaryButton,
                !canJoinQueue && { opacity: 0.6 },
                pressed && canJoinQueue && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.primaryButtonText}>{joining ? 'Joining queue...' : queueStatus ? 'Queue already active' : 'Join Queue'}</Text>
            </Pressable>

            <Pressable
              onPress={() => router.push('/screenviews/booking' as any)}
              style={({ pressed }) => [styles.secondaryButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.secondaryButtonText}>Book appointment instead</Text>
            </Pressable>
          </SectionCard>
        </View>
      </ScrollView>

      <SelectionSheet
        visible={serviceSheetOpen}
        title="Select services"
        subtitle="You can choose multiple services as long as they belong to the same specialization and are eligible for walk-in."
        options={serviceOptions}
        multiSelect
        selectedIds={selectedServiceIds}
        onClose={() => setServiceSheetOpen(false)}
        onSelect={toggleService}
      />

      <SelectionSheet
        visible={doctorSheetOpen}
        title="Select doctor"
        subtitle="Only doctors whose specialization matches the selected services and who are on active schedule right now are enabled."
        options={doctorOptions}
        selectedIds={selectedDoctorId ? [selectedDoctorId] : []}
        onClose={() => setDoctorSheetOpen(false)}
        onSelect={chooseDoctor}
      />
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
    backgroundColor:'rgba(255,255,255,0.07)',
  },
  pageScrollContent: {
    flexGrow: 1,
  },
  header: {
    backgroundColor: T.cyan700,
    paddingHorizontal: 20,
    paddingTop: 50,
    paddingBottom: 20,
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
  inlineSuccess: {
    fontSize: 12,
    color: T.green700,
    marginBottom: 10,
  },
  warningButton: {
    borderRadius: 999,
    backgroundColor: T.amber100,
    borderWidth: 1,
    borderColor: 'rgba(245,158,11,0.25)',
    paddingVertical: 11,
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 14,
  },
  warningButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.amber700,
  },
  infoRow: {
    flexDirection: 'row',
    gap: 8,
    marginBottom: 18,
    alignItems: 'stretch',
  },
  infoCard: {
    flex: 1,
  },
  infoCardInner: {
    flex: 1,
    backgroundColor: T.white,
    borderRadius: 16,
    padding: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    shadowColor: T.slate900,
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.06,
    shadowRadius: 8,
    elevation: 2,
    minHeight: 136,
    maxHeight: 260,
  },
  infoCardTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    marginBottom: 10,
  },
  infoCardTopCopy: {
    flex: 1,
  },
  infoCardScroll: {
    flex: 1,
  },
  infoCardScrollContent: {
    paddingBottom: 2,
  },
  infoIconCircle: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
    marginBottom: 8,
  },
  infoLabel: {
    fontSize: 9,
    fontWeight: '600',
    color: T.slate400,
    letterSpacing: 0.2,
    marginBottom: 4,
    lineHeight: 12,
  },
  infoValue: {
    fontSize: 13,
    fontWeight: '800',
    color: T.cyan700,
    lineHeight: 16,
    marginBottom: 3,
  },
  queueHeadline: {
    fontSize: 16,
    fontWeight: '800',
    color: T.cyan700,
    lineHeight: 20,
  },
  queueMetricRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 12,
    marginBottom: 6,
  },
  queueMetricLabel: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 15,
  },
  queueMetricValue: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate800,
    lineHeight: 15,
    textAlign: 'right',
    flexShrink: 1,
  },
  queueDetailsToggle: {
    marginTop: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 8,
    borderRadius: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  queueDetailsToggleText: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
  },
  queueDetailsPanel: {
    marginTop: 10,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    padding: 12,
  },
  queueServiceRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 10,
    marginBottom: 10,
  },
  queueServiceTitle: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 2,
  },
  queueServiceMeta: {
    fontSize: 10,
    color: T.slate500,
    lineHeight: 14,
  },
  queueServicePrice: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
  },
  queueTotalRow: {
    marginTop: 2,
    paddingTop: 10,
    borderTopWidth: 1,
    borderTopColor: T.slate200,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
  },
  queueTotalLabel: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate600,
    textTransform: 'uppercase',
    letterSpacing: 0.3,
  },
  queueTotalValue: {
    fontSize: 12,
    fontWeight: '800',
    color: T.cyan700,
  },
  infoSub: {
    fontSize: 9,
    color: T.slate500,
    lineHeight: 13,
  },
  sectionCard: {
    backgroundColor: T.white,
    borderRadius: 18,
    marginBottom: 12,
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
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 8,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  sectionHeaderMain: {
    flex: 1,
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 8,
  },
  sectionBadge: {
    backgroundColor: 'rgba(6,182,212,0.1)',
    borderRadius: 6,
    paddingHorizontal: 8,
    paddingVertical: 2,
  },
  sectionBadgeText: {
    fontSize: 9,
    fontWeight: '800',
    color: T.cyan700,
    letterSpacing: 0.5,
    textTransform: 'uppercase',
  },
  sectionTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T.slate800,
  },
  sectionSubtitle: {
    fontSize: 11,
    color: T.slate500,
    marginTop: 2,
    lineHeight: 15,
  },
  sectionSummaryWrap: {
    marginTop: 8,
  },
  sectionToggleButton: {
    width: 32,
    height: 32,
    borderRadius: 16,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    alignItems: 'center',
    justifyContent: 'center',
    marginLeft: 10,
  },
  sectionBody: {
    padding: 16,
  },
  summaryStack: {
    gap: 2,
  },
  summaryText: {
    fontSize: 11,
    color: T.slate600,
    lineHeight: 15,
    fontWeight: '600',
  },
  label: {
    fontSize: 11,
    fontWeight: '700',
    color: T.slate600,
    marginBottom: 6,
    textTransform: 'uppercase',
    letterSpacing: 0.4,
  },
  staticValueRow: {
    flexDirection: 'row',
    alignItems: 'center',
  },
  staticPill: {
    borderRadius: 999,
    paddingHorizontal: 12,
    paddingVertical: 8,
    backgroundColor: 'rgba(6,182,212,0.1)',
    borderWidth: 1,
    borderColor: 'rgba(6,182,212,0.18)',
  },
  staticPillText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.cyan700,
  },
  dropdownButton: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingHorizontal: 14,
    paddingVertical: 12,
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  dropdownButtonDisabled: {
    backgroundColor: T.slate50,
  },
  dropdownValue: {
    fontSize: 13,
    fontWeight: '600',
    color: T.slate800,
    marginBottom: 3,
  },
  dropdownValueMuted: {
    color: T.slate400,
  },
  dropdownHint: {
    fontSize: 10,
    lineHeight: 14,
    color: T.slate500,
  },
  helperText: {
    fontSize: 11,
    lineHeight: 16,
    color: T.slate500,
  },
  selectedServicesSection: {
    marginTop: 10,
  },
  selectedServicesTotal: {
    fontSize: 11,
    fontWeight: '700',
    color: T.cyan700,
    marginBottom: 6,
  },
  slideHint: {
    alignSelf: 'flex-end',
    fontSize: 10,
    fontWeight: '700',
    color: T.cyan700,
    marginBottom: 6,
  },
  selectedServicesRow: {
    paddingRight: 10,
    gap: 10,
  },
  selectedServiceCard: {
    width: 235,
    borderRadius: 16,
    backgroundColor: T.cyan100,
    borderWidth: 1,
    borderColor: 'rgba(8,145,178,0.16)',
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  selectedServiceTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: T.cyan700,
    marginBottom: 4,
  },
  selectedServiceDescription: {
    fontSize: 10,
    lineHeight: 14,
    color: T.slate700,
    marginBottom: 6,
  },
  selectedServiceMeta: {
    fontSize: 10,
    fontWeight: '700',
    color: T.slate700,
  },
  statusCard: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(34,197,94,0.2)',
    backgroundColor: T.green100,
    paddingHorizontal: 14,
    paddingVertical: 12,
    marginBottom: 14,
  },
  statusTitle: {
    fontSize: 12,
    fontWeight: '700',
    color: T.green700,
    marginBottom: 4,
  },
  statusSub: {
    fontSize: 11,
    lineHeight: 16,
    color: T.slate700,
  },
  reasonInput: {
    minHeight: 112,
    maxHeight: 160,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 13,
    lineHeight: 18,
    color: T.slate800,
  },
  primaryButton: {
    marginTop: 16,
    borderRadius: 999,
    backgroundColor: T.cyan700,
    paddingVertical: 13,
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
    paddingVertical: 13,
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate700,
  },
  modalBackdrop: {
    ...StyleSheet.absoluteFillObject,
    backgroundColor: 'rgba(15,23,42,0.45)',
  },
  sheet: {
    position: 'absolute',
    left: 0,
    right: 0,
    bottom: 0,
    maxHeight: '76%',
    backgroundColor: T.white,
    borderTopLeftRadius: 18,
    borderTopRightRadius: 18,
    borderWidth: 1,
    borderColor: T.slate200,
    overflow: 'hidden',
  },
  sheetHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    paddingHorizontal: 16,
    paddingVertical: 14,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  sheetTitle: {
    fontSize: 15,
    fontWeight: '700',
    color: T.slate900,
    marginBottom: 2,
  },
  sheetSubtitle: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 15,
  },
  sheetClose: {
    width: 32,
    height: 32,
    borderRadius: 16,
    alignItems: 'center',
    justifyContent: 'center',
    backgroundColor: T.slate50,
    borderWidth: 1,
    borderColor: T.slate200,
  },
  sheetScroll: {
    flex: 1,
  },
  sheetScrollContent: {
    padding: 16,
    gap: 10,
  },
  sheetOption: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingHorizontal: 14,
    paddingVertical: 12,
  },
  sheetOptionActive: {
    borderColor: T.cyan700,
    backgroundColor: T.cyan100,
  },
  sheetOptionDisabled: {
    backgroundColor: T.slate50,
    opacity: 0.65,
  },
  sheetOptionLabel: {
    fontSize: 13,
    fontWeight: '600',
    color: T.slate800,
    marginBottom: 2,
  },
  sheetOptionLabelActive: {
    color: T.cyan700,
  },
  sheetOptionLabelDisabled: {
    color: T.slate400,
  },
  sheetOptionDescription: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 15,
  },
  sheetOptionMeta: {
    marginTop: 4,
    fontSize: 10,
    fontWeight: '700',
    color: T.cyan700,
    lineHeight: 14,
  },
});
