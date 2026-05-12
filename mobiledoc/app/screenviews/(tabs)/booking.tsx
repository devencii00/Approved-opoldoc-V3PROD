import React, { useEffect, useMemo, useRef, useState } from 'react';
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
  red100: 'rgba(239,68,68,0.12)',
  red700: '#b91c1c',
  amber100: 'rgba(245,158,11,0.12)',
  amber700: '#b45309',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');
const ALLOWED_SERVICE_CATEGORIES = ['obstetrician - gynecologist', 'general surgeon'] as const;
const DAY_KEYS = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as const;
const DAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const MONTH_LABELS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

type DayKey = typeof DAY_KEYS[number];

type ServiceOption = {
  id: string;
  name: string;
  category: string;
  description: string;
  price: number | null;
  durationMinutes: number | null;
};

type DoctorOption = {
  id: string;
  name: string;
  specialization: string;
  scheduleDayKeys: DayKey[];
  hasAnySchedule: boolean;
};

type ScheduleSlot = {
  id: string;
  dayKey: DayKey;
  startTime: string;
  endTime: string;
  isAvailable: boolean;
};

type CalendarCell = {
  key: string;
  date: Date | null;
  inMonth: boolean;
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

function formatDoctorName(raw: any): string {
  const first = raw?.firstname ? String(raw.firstname) : '';
  const last = raw?.lastname ? String(raw.lastname) : '';
  const full = `Dr. ${[first, last].filter(Boolean).join(' ')}`.trim();
  return full === 'Dr.' ? 'Doctor' : full;
}

function formatTimeLabel(timeValue: string): string {
  const parts = String(timeValue).split(':');
  const hours = Number(parts[0] ?? 0);
  const minutes = Number(parts[1] ?? 0);
  const suffix = hours >= 12 ? 'PM' : 'AM';
  const normalizedHour = hours % 12 === 0 ? 12 : hours % 12;
  return `${normalizedHour}:${String(minutes).padStart(2, '0')} ${suffix}`;
}

function formatTimeRange(startTime: string, endTime: string): string {
  return `${formatTimeLabel(startTime)} - ${formatTimeLabel(endTime)}`;
}

function formatPriceLabel(value: number | null): string {
  if (typeof value !== 'number' || Number.isNaN(value)) return 'Price unavailable';
  return `P ${value.toFixed(2)}`;
}

function formatDurationLabel(value: number | null): string {
  if (typeof value !== 'number' || Number.isNaN(value) || value <= 0) return 'Duration unavailable';
  return `${value} mins`;
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

function toDateKey(date: Date): string {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, '0');
  const day = String(date.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function fromDateKey(dateKey: string): Date {
  const [year, month, day] = dateKey.split('-').map((value) => Number(value));
  return new Date(year, (month || 1) - 1, day || 1);
}

function formatDateLabel(dateKey: string): string {
  const date = fromDateKey(dateKey);
  return `${MONTH_LABELS[date.getMonth()]} ${date.getDate()}, ${date.getFullYear()}`;
}

function getDayKey(date: Date): DayKey {
  return DAY_KEYS[date.getDay()];
}

function buildCalendarCells(monthDate: Date): CalendarCell[] {
  const first = new Date(monthDate.getFullYear(), monthDate.getMonth(), 1);
  const start = new Date(first);
  start.setDate(first.getDate() - first.getDay());

  return Array.from({ length: 42 }, (_, index) => {
    const date = new Date(start);
    date.setDate(start.getDate() + index);
    return {
      key: toDateKey(date),
      date,
      inMonth: date.getMonth() === monthDate.getMonth(),
    };
  });
}

function minutesFromTime(value: string): number {
  const parts = String(value).split(':');
  return (Number(parts[0] ?? 0) * 60) + Number(parts[1] ?? 0);
}

export default function BookingScreen() {
  const router = useRouter();
  const [services, setServices] = useState<ServiceOption[]>([]);
  const [doctors, setDoctors] = useState<DoctorOption[]>([]);
  const [doctorSchedules, setDoctorSchedules] = useState<ScheduleSlot[]>([]);
  const [bookedTimeKeys, setBookedTimeKeys] = useState<string[]>([]);

  const [selectedServiceIds, setSelectedServiceIds] = useState<string[]>([]);
  const [selectedDoctorId, setSelectedDoctorId] = useState('');
  const [selectedDateKey, setSelectedDateKey] = useState('');
  const [selectedTimeKey, setSelectedTimeKey] = useState('');
  const [reason, setReason] = useState('');

  const [displayMonth, setDisplayMonth] = useState(() => {
    const now = new Date();
    return new Date(now.getFullYear(), now.getMonth(), 1);
  });
  const [stepOneCollapsed, setStepOneCollapsed] = useState(false);
  const [stepTwoCollapsed, setStepTwoCollapsed] = useState(false);
  const [serviceSheetOpen, setServiceSheetOpen] = useState(false);
  const [doctorSheetOpen, setDoctorSheetOpen] = useState(false);
  const [loading, setLoading] = useState(false);
  const [loadingSchedules, setLoadingSchedules] = useState(false);
  const [loadingSlots, setLoadingSlots] = useState(false);
  const [booking, setBooking] = useState(false);
  const [pageScrollEnabled, setPageScrollEnabled] = useState(true);
  const [hasActiveAppointment, setHasActiveAppointment] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const selectedServices = useMemo(
    () => services.filter((service) => selectedServiceIds.includes(service.id)),
    [selectedServiceIds, services]
  );
  const selectedCategory = selectedServices[0]?.category ?? '';
  const availableDayKeys = useMemo(
    () => new Set(doctorSchedules.filter((slot) => slot.isAvailable).map((slot) => slot.dayKey)),
    [doctorSchedules]
  );
  const calendarCells = useMemo(() => buildCalendarCells(displayMonth), [displayMonth]);
  const todayKey = useMemo(() => toDateKey(new Date()), []);
  const selectedDayKey = useMemo(() => (selectedDateKey ? getDayKey(fromDateKey(selectedDateKey)) : null), [selectedDateKey]);

  const filteredDoctors = useMemo(() => {
    if (!selectedCategory) return [];
    return doctors.filter((doctor) => {
      const specialization = normalizeCategory(doctor.specialization);
      return specialization.includes(selectedCategory) || selectedCategory.includes(specialization);
    });
  }, [doctors, selectedCategory]);

  const selectedDoctor = filteredDoctors.find((doctor) => doctor.id === selectedDoctorId) ?? null;
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

  const dateSlots = useMemo(() => {
    if (!selectedDateKey) return [];
    const dayKey = getDayKey(fromDateKey(selectedDateKey));
    const bookedSet = new Set(bookedTimeKeys);
    const isToday = selectedDateKey === todayKey;
    const nowMinutes = new Date().getHours() * 60 + new Date().getMinutes();

    return doctorSchedules
      .filter((slot) => slot.dayKey === dayKey && slot.isAvailable)
      .sort((a, b) => minutesFromTime(a.startTime) - minutesFromTime(b.startTime))
      .map((slot) => {
        const timeKey = `${selectedDateKey} ${slot.startTime.slice(0, 5)}`;
        const slotMinutes = minutesFromTime(slot.startTime);
        const booked = bookedSet.has(timeKey);
        const inPast = isToday && slotMinutes <= nowMinutes;
        return {
          ...slot,
          timeKey,
          label: formatTimeRange(slot.startTime, slot.endTime),
          disabled: booked || inPast,
          booked,
        };
      });
  }, [bookedTimeKeys, doctorSchedules, selectedDateKey, todayKey]);

  const selectedTimeSlot = dateSlots.find((slot) => slot.timeKey === selectedTimeKey) ?? null;
  const stepTwoSummary = useMemo(() => {
    if (!selectedDateKey || !selectedTimeSlot) return null;

    return (
      <View style={styles.summaryStack}>
        <Text style={styles.summaryText}>{formatDateLabel(selectedDateKey)}</Text>
        <Text style={styles.summaryText}>{selectedTimeSlot.label}</Text>
      </View>
    );
  }, [selectedDateKey, selectedTimeSlot]);

  const doctorOptions: DropdownOption[] = filteredDoctors.map((doctor) => {
    const hasScheduleForSelectedDay = selectedDayKey ? doctor.scheduleDayKeys.includes(selectedDayKey) : doctor.hasAnySchedule;
    const noSchedule = !doctor.hasAnySchedule || !hasScheduleForSelectedDay;
    return {
      id: doctor.id,
      label: doctor.name,
      description: noSchedule
        ? `${doctor.specialization} • No schedule`
        : doctor.specialization,
      disabled: noSchedule,
    };
  });

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

  useEffect(() => {
    let cancelled = false;

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

        const [servicesRes, doctorsRes, activeRes] = await Promise.all([
          fetch(`${API_BASE_URL}/services?per_page=100`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/doctors?per_page=100&available_only=1`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/appointments/active-exists`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
        ]);

        const [servicesData, doctorsData, activeData] = await Promise.all([
          servicesRes.json().catch(() => ({})),
          doctorsRes.json().catch(() => ({})),
          activeRes.json().catch(() => ({})),
        ]);

        if (!servicesRes.ok || !doctorsRes.ok || !activeRes.ok) {
          const anyMessage = servicesData?.message || doctorsData?.message || activeData?.message;
          if (!cancelled) {
            setError(typeof anyMessage === 'string' && anyMessage.length > 0 ? anyMessage : 'Unable to load booking options.');
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
          .filter((service: ServiceOption) => service.id.length > 0 && ALLOWED_SERVICE_CATEGORIES.includes(service.category as (typeof ALLOWED_SERVICE_CATEGORIES)[number]));

        const mappedDoctors: DoctorOption[] = rawDoctors
          .map((doctor: any) => {
            const rawSchedules = Array.isArray(doctor?.doctor_schedules)
              ? doctor.doctor_schedules
              : Array.isArray(doctor?.doctorSchedules)
                ? doctor.doctorSchedules
                : [];
            const availableSchedules = rawSchedules.filter((slot: any) => Boolean(slot?.is_available));
            const scheduleDayKeys = Array.from(
              new Set(
                availableSchedules
                  .map((slot: any) => (typeof slot?.day_of_week === 'string' ? slot.day_of_week : ''))
                  .filter((day: string) => DAY_KEYS.includes(day as DayKey))
              )
            ) as DayKey[];

            return {
              id: String(doctor?.user_id ?? ''),
              name: formatDoctorName(doctor),
              specialization: typeof doctor?.specialization === 'string' ? doctor.specialization : 'Doctor',
              scheduleDayKeys,
              hasAnySchedule: scheduleDayKeys.length > 0,
            };
          })
          .filter((doctor: DoctorOption) => doctor.id.length > 0);

        if (!cancelled) {
          setServices(mappedServices);
          setDoctors(mappedDoctors);
          setHasActiveAppointment(Boolean(activeData?.exists));
          if (activeData?.exists) {
            setError('You already have an active appointment.');
          }
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    loadInitialData();
    return () => {
      cancelled = true;
    };
  }, [router]);

  useEffect(() => {
    let cancelled = false;

    async function loadSchedules() {
      if (!selectedDoctorId) {
        setDoctorSchedules([]);
        setBookedTimeKeys([]);
        return;
      }

      setLoadingSchedules(true);
      setError((current) => (current === 'Selected doctor schedule could not be loaded.' ? '' : current));

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          router.replace('/screenviews/aut-landing/login-screen' as any);
          return;
        }

        const response = await fetch(`${API_BASE_URL}/doctor-schedules?doctor_id=${selectedDoctorId}&available_only=1&per_page=100`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          if (!cancelled) setError(typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Selected doctor schedule could not be loaded.');
          return;
        }

        const raw = Array.isArray(data?.data) ? data.data : [];
        const mapped: ScheduleSlot[] = raw
          .map((slot: any) => ({
            id: String(slot?.schedule_id ?? ''),
            dayKey: (typeof slot?.day_of_week === 'string' ? slot.day_of_week : 'mon') as DayKey,
            startTime: typeof slot?.start_time === 'string' ? slot.start_time : '00:00:00',
            endTime: typeof slot?.end_time === 'string' ? slot.end_time : '00:00:00',
            isAvailable: Boolean(slot?.is_available),
          }))
          .filter((slot: ScheduleSlot) => slot.id.length > 0);

        if (!cancelled) {
          setDoctorSchedules(mapped);
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoadingSchedules(false);
      }
    }

    loadSchedules();
    return () => {
      cancelled = true;
    };
  }, [router, selectedDoctorId]);

  useEffect(() => {
    let cancelled = false;

    async function loadBookedSlots() {
      if (!selectedDoctorId || !selectedDateKey) {
        setBookedTimeKeys([]);
        return;
      }

      setLoadingSlots(true);
      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          router.replace('/screenviews/aut-landing/login-screen' as any);
          return;
        }

        const params = new URLSearchParams({
          doctor_id: selectedDoctorId,
          appointment_type: 'scheduled',
          start_date: selectedDateKey,
          end_date: selectedDateKey,
          per_page: '100',
        });

        const response = await fetch(`${API_BASE_URL}/appointments?${params.toString()}`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          if (!cancelled) setError(typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to load booked slots.');
          return;
        }

        const booked = (Array.isArray(data?.data) ? data.data : [])
          .filter((appointment: any) => appointment?.appointment_datetime && appointment?.status !== 'cancelled')
          .map((appointment: any) => {
            const date = new Date(appointment.appointment_datetime);
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${selectedDateKey} ${hours}:${minutes}`;
          });

        if (!cancelled) {
          setBookedTimeKeys(Array.from(new Set(booked)));
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoadingSlots(false);
      }
    }

    loadBookedSlots();
    return () => {
      cancelled = true;
    };
  }, [router, selectedDateKey, selectedDoctorId]);

  function isDateSelectable(date: Date | null, inMonth: boolean): boolean {
    if (!date || !inMonth || !selectedDoctorId) return false;
    if (toDateKey(date) < todayKey) return false;
    return availableDayKeys.has(getDayKey(date));
  }

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
        setSelectedDateKey('');
        setSelectedTimeKey('');
        setDoctorSchedules([]);
        setBookedTimeKeys([]);
        setStepOneCollapsed(false);
        setStepTwoCollapsed(false);
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
    setSelectedDateKey('');
    setSelectedTimeKey('');
    setBookedTimeKeys([]);
    setSuccess('');
    setError('');
    setStepOneCollapsed(true);
    setStepTwoCollapsed(false);
  }

  async function refreshActiveAppointmentStatus(): Promise<boolean> {
    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      router.replace('/screenviews/aut-landing/login-screen' as any);
      return true;
    }

    const response = await fetch(`${API_BASE_URL}/appointments/active-exists`, {
      headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
      throw new Error(typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to verify active appointments.');
    }

    const exists = Boolean(data?.exists);
    setHasActiveAppointment(exists);
    return exists;
  }

  async function handleBookAppointment() {
    setError('');
    setSuccess('');

    if (hasActiveAppointment) {
      setError('You already have an active appointment.');
      return;
    }

    if (selectedServiceIds.length === 0) {
      setError('Please choose at least one service.');
      return;
    }
    if (!selectedDoctorId) {
      setError('Please choose a doctor.');
      return;
    }
    if (!selectedDateKey) {
      setError('Please choose a date.');
      return;
    }
    if (!selectedTimeSlot) {
      setError('Please choose an available time slot.');
      return;
    }

    setBooking(true);
    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        router.replace('/screenviews/aut-landing/login-screen' as any);
        return;
      }

      const exists = await refreshActiveAppointmentStatus();
      if (exists) {
        setError('You already have an active appointment.');
        return;
      }

      const appointmentDateTime = `${selectedDateKey} ${selectedTimeSlot.startTime.slice(0, 8)}`;
      const response = await fetch(`${API_BASE_URL}/appointments`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          doctor_id: Number(selectedDoctorId),
          appointment_type: 'scheduled',
          appointment_datetime: appointmentDateTime,
          reason_for_visit: reason.trim().length > 0 ? reason.trim() : null,
          service_ids: selectedServiceIds.map((id) => Number(id)),
        }),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message = typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to book appointment.';
        setError(message);
        if (data?.code === 'ACTIVE_APPOINTMENT_EXISTS') {
          setHasActiveAppointment(true);
        }
        return;
      }

      setHasActiveAppointment(true);
      setSuccess('Appointment booked successfully.');
      setSelectedServiceIds([]);
      setSelectedDoctorId('');
      setSelectedDateKey('');
      setSelectedTimeKey('');
      setReason('');
      setDoctorSchedules([]);
      setBookedTimeKeys([]);
      setStepOneCollapsed(false);
      setStepTwoCollapsed(false);
    } catch (requestError) {
      const message = requestError instanceof Error ? requestError.message : 'Network error. Please try again.';
      setError(message);
    } finally {
      setBooking(false);
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
        nestedScrollEnabled
        scrollEnabled={pageScrollEnabled}
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
              <Text style={styles.headerTitle}>Book Appointment</Text>
              <Text style={styles.headerGreeting}>Schedule a visit with the right specialist.</Text>
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


          <SectionCard
            title="Appointment Details"
            subtitle=""
            badge="Step 1"
            delay={80}
            collapsed={stepOneCollapsed}
            summary={stepOneSummary}
            onToggleCollapse={() => setStepOneCollapsed((current) => !current)}
          >
            <Text style={styles.label}>Appointment type</Text>
            <View style={styles.staticValueRow}>
              <View style={styles.staticPill}>
                <Text style={styles.staticPillText}>Scheduled</Text>
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
                <Text style={styles.dropdownHint}></Text>
              </View>
              <Ionicons name="chevron-down" size={18} color={T.slate500} />
            </Pressable>

            {selectedServices.length > 0 ? (
              <View style={styles.selectedServicesSection}>
                {selectedServices.length > 1 ? <Text style={styles.slideHint}>Slide to see more</Text> : null}
                <ScrollView
                  horizontal
                  nestedScrollEnabled
                  directionalLockEnabled
                  showsHorizontalScrollIndicator={false}
                  style={styles.selectedServicesScroller}
                  contentContainerStyle={styles.selectedServicesRow}
                  onTouchStart={() => setPageScrollEnabled(false)}
                  onTouchEnd={() => setPageScrollEnabled(true)}
                  onTouchCancel={() => setPageScrollEnabled(true)}
                  onScrollEndDrag={() => setPageScrollEnabled(true)}
                  onMomentumScrollEnd={() => setPageScrollEnabled(true)}
                >
                  {selectedServices.map((service) => (
                    <View key={service.id} style={styles.selectedServiceCard}>
                      <Text style={styles.selectedServiceTitle}>{service.name}</Text>
                      <Text style={styles.selectedServiceDescription}>
                        {getServiceDescription(service)}
                      </Text>
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
                  {selectedCategory ? 'Doctors are filtered by specialization match.' : 'Select at least one service to unlock doctors.'}
                </Text>
              </View>
              <Ionicons name="chevron-down" size={18} color={selectedCategory ? T.slate500 : T.slate300} />
            </Pressable>
          </SectionCard>

          <SectionCard
            title="Date and Time"
            subtitle=""
            badge="Step 2"
            delay={120}
            collapsed={stepTwoCollapsed}
            summary={stepTwoSummary}
            onToggleCollapse={() => setStepTwoCollapsed((current) => !current)}
          >
            <View style={styles.monthHeader}>
              <Pressable
                onPress={() => setDisplayMonth((current) => new Date(current.getFullYear(), current.getMonth() - 1, 1))}
                style={({ pressed }) => [styles.monthNavBtn, pressed && { opacity: 0.85 }]}
              >
                <Ionicons name="chevron-back" size={16} color={T.slate700} />
              </Pressable>
              <Text style={styles.monthTitle}>{`${MONTH_LABELS[displayMonth.getMonth()]} ${displayMonth.getFullYear()}`}</Text>
              <Pressable
                onPress={() => setDisplayMonth((current) => new Date(current.getFullYear(), current.getMonth() + 1, 1))}
                style={({ pressed }) => [styles.monthNavBtn, pressed && { opacity: 0.85 }]}
              >
                <Ionicons name="chevron-forward" size={16} color={T.slate700} />
              </Pressable>
            </View>

            <View style={styles.calendarWeekRow}>
              {DAY_LABELS.map((label) => (
                <Text key={label} style={styles.calendarWeekLabel}>
                  {label}
                </Text>
              ))}
            </View>

            <View style={styles.calendarGrid}>
              {calendarCells.map((cell) => {
                const selectable = isDateSelectable(cell.date, cell.inMonth);
                const dateKey = cell.date ? toDateKey(cell.date) : '';
                const selected = dateKey === selectedDateKey;

                return (
                  <View key={cell.key} style={styles.calendarCellWrap}>
                    <Pressable
                      disabled={!selectable}
                      onPress={() => {
                        if (!cell.date) return;
                        setSelectedDateKey(dateKey);
                        setSelectedTimeKey('');
                        setSuccess('');
                        setError('');
                        setStepTwoCollapsed(false);
                      }}
                      style={({ pressed }) => [
                        styles.calendarCell,
                        !cell.inMonth && styles.calendarCellOutside,
                        selectable && styles.calendarCellAvailable,
                        selected && styles.calendarCellSelected,
                        pressed && selectable && { opacity: 0.85 },
                      ]}
                    >
                      <Text
                        style={[
                          styles.calendarCellText,
                          !cell.inMonth && styles.calendarCellTextOutside,
                          selectable && styles.calendarCellTextAvailable,
                          selected && styles.calendarCellTextSelected,
                        ]}
                      >
                        {cell.date ? String(cell.date.getDate()) : ''}
                      </Text>
                    </Pressable>
                  </View>
                );
              })}
            </View>

            <View style={styles.slotHeader}>
              <Text style={styles.label}>Time slot</Text>
              {(loadingSchedules || loadingSlots) ? <ActivityIndicator size="small" color={T.cyan700} /> : null}
            </View>

            {!selectedDoctorId ? (
              <Text style={styles.helperText}>Choose a doctor first to unlock the calendar and time slots.</Text>
            ) : !selectedDateKey ? (
              <Text style={styles.helperText}>Select an enabled date to see available appointment slots.</Text>
            ) : dateSlots.length === 0 ? (
              <Text style={styles.helperText}>No time slots are available on this date.</Text>
            ) : (
              <View style={styles.slotWrap}>
                {dateSlots.map((slot) => (
                  <Pressable
                    key={slot.id}
                    disabled={slot.disabled}
                    onPress={() => {
                      setSelectedTimeKey(slot.timeKey);
                      setSuccess('');
                      setError('');
                      setStepTwoCollapsed(true);
                    }}
                    style={({ pressed }) => [
                      styles.slotChip,
                      slot.disabled && styles.slotChipDisabled,
                      selectedTimeKey === slot.timeKey && styles.slotChipActive,
                      pressed && !slot.disabled && { opacity: 0.85 },
                    ]}
                  >
                    <Text
                      style={[
                        styles.slotChipText,
                        slot.disabled && styles.slotChipTextDisabled,
                        selectedTimeKey === slot.timeKey && styles.slotChipTextActive,
                      ]}
                    >
                      {slot.label}
                    </Text>
                    {slot.booked ? <Text style={styles.bookedFlag}>Booked</Text> : null}
                  </Pressable>
                ))}
              </View>
            )}
          </SectionCard>

          <SectionCard
            title="Reason"
            subtitle="Optional notes help the clinic understand your concern before the visit."
            badge="Step 3"
            delay={160}
            style={{ marginBottom: 20 }}
          >
            <TextInput
              value={reason}
              onChangeText={setReason}
              placeholder="Add a reason for the appointment"
              placeholderTextColor={T.slate400}
              multiline
              textAlignVertical="top"
              scrollEnabled
              style={styles.reasonInput}
            />

            <Pressable
              disabled={loading || booking}
              onPress={handleBookAppointment}
              style={({ pressed }) => [
                styles.primaryButton,
                (loading || booking) && { opacity: 0.6 },
                pressed && !(loading || booking) && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.primaryButtonText}>{booking ? 'Booking appointment...' : 'Book Appointment'}</Text>
            </Pressable>

               <Pressable
                          onPress={() => router.push('/screenviews/queue' as any)}
                          style={({ pressed }) => [styles.secondaryButton, pressed && { opacity: 0.85 }]}
                        >
                          <Text style={styles.secondaryButtonText}>Join queue instead</Text>
                        </Pressable>
          </SectionCard>
        </View>
      </ScrollView>

      <SelectionSheet
        visible={serviceSheetOpen}
        title="Select services"
        subtitle="You can choose multiple services as long as they belong to the same specialization."
        options={serviceOptions}
        multiSelect
        selectedIds={selectedServiceIds}
        onClose={() => setServiceSheetOpen(false)}
        onSelect={toggleService}
      />

      <SelectionSheet
        visible={doctorSheetOpen}
        title="Select doctor"
        subtitle="Only doctors whose specialization matches the selected services are shown."
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
  selectedServicesSection: {
    marginTop: 10,
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
  selectedServicesScroller: {
    marginHorizontal: -2,
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
  monthHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    marginBottom: 12,
  },
  monthNavBtn: {
    width: 34,
    height: 34,
    borderRadius: 17,
    alignItems: 'center',
    justifyContent: 'center',
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
  },
  monthTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T.slate800,
  },
  calendarWeekRow: {
    flexDirection: 'row',
    marginBottom: 8,
  },
  calendarWeekLabel: {
    flex: 1,
    textAlign: 'center',
    fontSize: 10,
    fontWeight: '700',
    color: T.slate400,
    textTransform: 'uppercase',
  },
  calendarGrid: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    marginHorizontal: -2,
    marginTop: 2,
  },
  calendarCellWrap: {
    width: '14.2857%',
    paddingHorizontal: 2,
    paddingVertical: 2,
  },
  calendarCell: {
    width: '100%',
    aspectRatio: 1,
    borderRadius: 10,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    alignItems: 'center',
    justifyContent: 'center',
  },
  calendarCellOutside: {
    backgroundColor: T.slate50,
    borderColor: T.slate100,
  },
  calendarCellAvailable: {
    backgroundColor: T.white,
  },
  calendarCellSelected: {
    backgroundColor: T.cyan700,
    borderColor: T.cyan700,
  },
  calendarCellText: {
    fontSize: 11,
    fontWeight: '600',
    color: T.slate300,
  },
  calendarCellTextOutside: {
    color: T.slate300,
  },
  calendarCellTextAvailable: {
    color: T.slate700,
  },
  calendarCellTextSelected: {
    color: T.white,
  },
  slotHeader: {
    marginTop: 16,
    marginBottom: 8,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
  },
  helperText: {
    fontSize: 11,
    lineHeight: 16,
    color: T.slate500,
  },
  slotWrap: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 10,
  },
  slotChip: {
    minWidth: '48%',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingHorizontal: 12,
    paddingVertical: 10,
  },
  slotChipDisabled: {
    backgroundColor: T.slate50,
    borderColor: T.slate200,
  },
  slotChipActive: {
    backgroundColor: T.cyan100,
    borderColor: T.cyan700,
  },
  slotChipText: {
    fontSize: 12,
    fontWeight: '600',
    color: T.slate700,
  },
  slotChipTextDisabled: {
    color: T.slate400,
  },
  slotChipTextActive: {
    color: T.cyan700,
  },
  bookedFlag: {
    marginTop: 4,
    fontSize: 10,
    fontWeight: '700',
    color: T.red700,
    textTransform: 'uppercase',
    letterSpacing: 0.4,
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
