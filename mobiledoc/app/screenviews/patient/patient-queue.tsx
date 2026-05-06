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
  green100: 'rgba(34,197,94,0.12)',
  green700: '#15803d',
  amber100: 'rgba(245,158,11,0.12)',
  amber700: '#b45309',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type DoctorListItem = {
  id: string;
  name: string;
};

type QueueStatus = {
  queueId: string;
  queueNumber: string;
  status: 'waiting' | 'serving' | 'done' | 'cancelled';
  doctor: string;
};

export default function PatientQueueScreen() {
  const router = useRouter();

  const [doctors, setDoctors] = useState<DoctorListItem[]>([]);
  const [selectedDoctorId, setSelectedDoctorId] = useState<string>('');
  const [reason, setReason] = useState('');

  const [queueStatus, setQueueStatus] = useState<QueueStatus | null>(null);
  const [hasPendingQueueRequest, setHasPendingQueueRequest] = useState(false);
  const [joining, setJoining] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [loading, setLoading] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [needsMedicalBackground, setNeedsMedicalBackground] = useState(false);

  const canRequest = useMemo(() => {
    if (joining || submitting || loading) return false;
    if (queueStatus) return false;
    if (hasPendingQueueRequest) return false;
    return selectedDoctorId.length > 0;
  }, [hasPendingQueueRequest, joining, loading, queueStatus, selectedDoctorId, submitting]);

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

        const [doctorsRes, queuesRes, queueRequestsRes] = await Promise.all([
          fetch(`${API_BASE_URL}/doctors?per_page=100`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/queues?per_page=10`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
          fetch(`${API_BASE_URL}/appointments?queue_request_only=1&per_page=1`, {
            headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
          }),
        ]);

        const [doctorsData, queuesData, queueRequestsData] = await Promise.all([
          doctorsRes.json().catch(() => ({})),
          queuesRes.json().catch(() => ({})),
          queueRequestsRes.json().catch(() => ({})),
        ]);

        if (!doctorsRes.ok || !queuesRes.ok || !queueRequestsRes.ok) {
          const anyMessage = doctorsData?.message || queuesData?.message || queueRequestsData?.message;
          setError(typeof anyMessage === 'string' && anyMessage.length > 0 ? anyMessage : 'Unable to load queue data.');
          return;
        }

        const rawDoctors = Array.isArray(doctorsData?.data) ? doctorsData.data : Array.isArray(doctorsData) ? doctorsData : [];
        const mappedDoctors: DoctorListItem[] = rawDoctors.map((d: any) => {
          const first = d?.firstname ? String(d.firstname) : '';
          const last = d?.lastname ? String(d.lastname) : '';
          const name = `Dr. ${[first, last].filter(Boolean).join(' ')}`.trim();
          return { id: String(d.user_id ?? d.id ?? ''), name: name === 'Dr.' ? 'Doctor' : name };
        }).filter((d: DoctorListItem) => d.id.length > 0);

        const queueRaw = Array.isArray(queuesData?.data) ? queuesData.data : [];
        const activeQueue = queueRaw.find((q: any) => q?.status === 'waiting' || q?.status === 'serving') ?? null;
        const mappedQueue: QueueStatus | null = activeQueue
          ? {
              queueId: String(activeQueue.queue_id ?? ''),
              queueNumber: activeQueue.queue_number != null ? String(activeQueue.queue_number) : '',
              status: activeQueue.status === 'serving' ? 'serving' : 'waiting',
              doctor: (() => {
                const doctorFirst = activeQueue?.appointment?.doctor?.firstname
                  ? String(activeQueue.appointment.doctor.firstname)
                  : '';
                const doctorLast = activeQueue?.appointment?.doctor?.lastname ? String(activeQueue.appointment.doctor.lastname) : '';
                const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();
                return doctorName === 'Dr.' ? 'Doctor' : doctorName;
              })(),
            }
          : null;

        const pendingQueueRequestCount =
          typeof queueRequestsData?.total === 'number'
            ? queueRequestsData.total
            : Array.isArray(queueRequestsData?.data)
              ? queueRequestsData.data.length
              : 0;

        if (!cancelled) {
          setDoctors(mappedDoctors);
          setQueueStatus(mappedQueue);
          setHasPendingQueueRequest(pendingQueueRequestCount > 0);
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

  async function handleJoinQueue() {
    if (!canRequest) {
      if (!selectedDoctorId) setError('Please select a doctor.');
      return;
    }

    setError('');
    setSuccess('');
    setNeedsMedicalBackground(false);
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
        }),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message =
          typeof (data as any)?.message === 'string' && (data as any).message.length > 0
            ? (data as any).message
            : 'Unable to join the queue.';
        setError(message);
        return;
      }

      const doctorFirst = (data as any)?.appointment?.doctor?.firstname ? String((data as any).appointment.doctor.firstname) : '';
      const doctorLast = (data as any)?.appointment?.doctor?.lastname ? String((data as any).appointment.doctor.lastname) : '';
      const doctorName = `Dr. ${[doctorFirst, doctorLast].filter(Boolean).join(' ')}`.trim();

      setQueueStatus({
        queueId: String((data as any)?.queue_id ?? ''),
        queueNumber: (data as any)?.queue_number != null ? String((data as any).queue_number) : '',
        status: (data as any)?.status === 'serving' ? 'serving' : 'waiting',
        doctor: doctorName === 'Dr.' ? 'Doctor' : doctorName,
      });
      setHasPendingQueueRequest(false);
      setSelectedDoctorId('');
      setReason('');
      setSuccess('You have joined the queue.');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setJoining(false);
    }
  }

  async function handleRequestQueue() {
    if (!canRequest) {
      if (!selectedDoctorId) setError('Please select a doctor.');
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
          appointment_type: 'scheduled',
          queue_request: true,
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
              : 'Medical background is required before requesting a queue entry.'
          );
          return;
        }
        const message =
          typeof data?.message === 'string' && data.message.length > 0
            ? data.message
            : 'Unable to request queue entry.';
        setError(message);
        return;
      }

      setSuccess('Queue request submitted. Please wait for approval.');
      setHasPendingQueueRequest(true);
      setSelectedDoctorId('');
      setReason('');
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
            <Text style={styles.headerTitle}>Queue</Text>
            <Text style={styles.headerSub}>Request entry and track your status.</Text>
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
              <Text style={styles.eyebrowText}>Status</Text>
            </View>
            <Text style={styles.cardTitle}>Current queue</Text>
            <Text style={styles.cardSubtitle}>Shows an active queue entry or pending approval.</Text>
          </View>

          <View style={styles.cardBody}>
            <View style={styles.statusRow}>
              <View style={styles.statusMain}>
                <Text style={styles.statusTitle}>
                  {queueStatus
                    ? `In queue · #${queueStatus.queueNumber || '—'}`
                    : hasPendingQueueRequest
                      ? 'Pending approval'
                      : 'No active queue'}
                </Text>
                <Text style={styles.statusSub}>
                  {queueStatus
                    ? `${queueStatus.doctor} · ${queueStatus.status === 'serving' ? 'Now serving' : 'Waiting'}`
                    : hasPendingQueueRequest
                      ? 'Your request is waiting for approval.'
            : 'Join the queue below or submit a request for approval.'}
                </Text>
              </View>
              <View
                style={[
                  styles.statusPill,
                  queueStatus && queueStatus.status === 'serving'
                    ? { backgroundColor: T.green100, borderColor: 'rgba(34,197,94,0.25)' }
                    : hasPendingQueueRequest
                      ? { backgroundColor: T.amber100, borderColor: 'rgba(245,158,11,0.25)' }
                      : { backgroundColor: T.slate50, borderColor: T.slate200 },
                ]}
              >
                <Text
                  style={[
                    styles.statusPillText,
                    queueStatus && queueStatus.status === 'serving'
                      ? { color: T.green700 }
                      : hasPendingQueueRequest
                        ? { color: T.amber700 }
                        : { color: T.slate600 },
                  ]}
                >
                  {queueStatus ? (queueStatus.status === 'serving' ? 'Serving' : 'Waiting') : hasPendingQueueRequest ? 'Pending' : 'Idle'}
                </Text>
              </View>
            </View>
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Request</Text>
            </View>
            <Text style={styles.cardTitle}>Request queue entry</Text>
            <Text style={styles.cardSubtitle}>Select a doctor and optionally provide a reason.</Text>
          </View>

          <View style={styles.cardBody}>
            <Text style={styles.label}>Doctor</Text>
            <View style={styles.doctorList}>
              {doctors.map((doc) => (
                <Pressable
                  key={doc.id}
                  onPress={() => setSelectedDoctorId(doc.id)}
                  style={({ pressed }) => [
                    styles.doctorChip,
                    selectedDoctorId === doc.id && styles.doctorChipActive,
                    pressed && { opacity: 0.85 },
                  ]}
                >
                  <Text style={[styles.doctorChipText, selectedDoctorId === doc.id && styles.doctorChipTextActive]}>
                    {doc.name}
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
              onPress={handleJoinQueue}
              disabled={!canRequest}
              style={({ pressed }) => [
                styles.primaryButton,
                (!canRequest || joining) && { opacity: 0.5 },
                pressed && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.primaryButtonText}>
                {joining ? 'Joining...' : hasPendingQueueRequest || queueStatus ? 'Action already active' : 'Join Queue'}
              </Text>
            </Pressable>

            <Pressable
              onPress={handleRequestQueue}
              disabled={!canRequest}
              style={({ pressed }) => [
                styles.secondaryButton,
                (!canRequest || submitting) && { opacity: 0.5 },
                pressed && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.secondaryButtonText}>
                {submitting ? 'Submitting...' : hasPendingQueueRequest || queueStatus ? 'Action already active' : 'Request Approval Instead'}
              </Text>
            </Pressable>

            <Pressable
              onPress={() => router.push('/screenviews/patient/patient-book-appointment' as any)}
              style={({ pressed }) => [styles.secondaryButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.secondaryButtonText}>Book appointment instead</Text>
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
  statusRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 12,
  },
  statusMain: {
    flex: 1,
  },
  statusTitle: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 2,
  },
  statusSub: {
    fontSize: 11,
    color: T.slate500,
    lineHeight: 15,
  },
  statusPill: {
    paddingHorizontal: 10,
    paddingVertical: 7,
    borderRadius: 999,
    borderWidth: 1,
  },
  statusPillText: {
    fontSize: 11,
    fontWeight: '700',
  },
  label: {
    fontSize: 11,
    fontWeight: '600',
    color: T.slate600,
    marginBottom: 6,
  },
  doctorList: {
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
