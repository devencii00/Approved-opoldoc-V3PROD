import React, { useEffect, useMemo, useState, useRef } from 'react';
import {
  View,
  Text,
  StyleSheet,
  Pressable,
  ScrollView,
  StatusBar,
  SafeAreaView,
  ActivityIndicator,
  Animated,
  Platform,
} from 'react-native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
// @ts-ignore
import * as DocumentPicker from 'expo-document-picker';
import { persistCurrentUser } from '@/lib/auth-storage';

// ─── Design Tokens ───────────────────────────────────────────────────────────
const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  cyan400: '#22d3ee',
  cyan100: '#cffafe',
  slate50:  '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate300: '#cbd5e1',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate600: '#475569',
  slate700: '#334155',
  slate800: '#1e293b',
  slate900: '#0f172a',
  white:    '#ffffff',
  green100: 'rgba(34,197,94,0.12)',
  green700: '#15803d',
  red100:   'rgba(239,68,68,0.12)',
  red700:   '#b91c1c',
  amber100: 'rgba(245,158,11,0.12)',
  amber700: '#b45309',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type VerificationRequest = {
  verification_id: number;
  type: 'none' | 'senior' | 'pwd' | 'pregnant';
  status: 'pending' | 'approved' | 'rejected';
  document_path: string | null;
  remarks: string | null;
  verified_at: string | null;
};

type PickedDoc = {
  uri: string;
  name: string;
  mimeType: string;
  file?: File | null;
};

// ─── Animated Card ────────────────────────────────────────────────────────────
function AnimatedCard({ children, delay = 0, style }: { children: React.ReactNode; delay?: number; style?: any }) {
  const anim = useRef(new Animated.Value(0)).current;
  useEffect(() => {
    Animated.timing(anim, {
      toValue: 1,
      duration: 480,
      delay,
      useNativeDriver: true,
    }).start();
  }, []);
  return (
    <Animated.View
      style={[
        {
          opacity: anim,
          transform: [{ translateY: anim.interpolate({ inputRange: [0, 1], outputRange: [18, 0] }) }],
        },
        style,
      ]}
    >
      {children}
    </Animated.View>
  );
}

export default function PatientVerificationScreen() {
  const router = useRouter();
  const isOnboarding = Boolean((globalThis as any)?.currentUser?.is_first_login);
  const [loading, setLoading] = useState(false);
  const [submitting, setSubmitting] = useState(false);
  const [verificationType, setVerificationType] = useState<'none' | 'senior' | 'pwd' | 'pregnant'>('none');
  const [verificationDoc, setVerificationDoc] = useState<PickedDoc | null>(null);
  const [verificationItems, setVerificationItems] = useState<VerificationRequest[]>([]);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [typeMenuOpen, setTypeMenuOpen] = useState(false);

  const latestRejected = useMemo(() => {
    return verificationItems.find(v => v.status === 'rejected');
  }, [verificationItems]);
  const hasPendingVerification = useMemo(() => {
    return verificationItems.some(v => v.status === 'pending');
  }, [verificationItems]);

  useEffect(() => {
    let cancelled = false;

    async function loadVerifications() {
      setLoading(true);
      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) return;

        const response = await fetch(`${API_BASE_URL}/patient-verifications?per_page=10`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) return;

        const list: VerificationRequest[] = Array.isArray((data as any)?.data)
          ? (data as any).data
          : Array.isArray(data)
            ? data
            : [];

        if (!cancelled) setVerificationItems(list);
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    loadVerifications();
    return () => {
      cancelled = true;
    };
  }, []);

  async function handlePickDoc() {
    setError('');
    setSuccess('');
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: [
          'image/*',
          'application/pdf',
          'application/msword',
          'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ],
        copyToCacheDirectory: true,
        multiple: false,
      });

      if (result.canceled) return;
      const asset = result.assets && result.assets.length ? result.assets[0] : null;
      if (!asset) {
        setError('Unable to read selected document.');
        return;
      }

      setVerificationDoc({
        uri: asset.uri,
        name: asset.name,
        mimeType: asset.mimeType ?? 'application/octet-stream',
        file: (asset as any).file instanceof File ? (asset as any).file : null,
      });
    } catch {
      setError('Unable to pick a document.');
    }
  }

  async function handleSubmit() {
    if (hasPendingVerification) {
      setError('You already have a pending verification request.');
      return;
    }
    if (!verificationDoc) {
      setError('Please upload a document first.');
      return;
    }

    setError('');
    setSuccess('');
    setSubmitting(true);

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const pickedDoc = verificationDoc;
      if (!pickedDoc) {
        setError('Please upload a document first.');
        return;
      }

      const formData = new FormData();
      formData.append('type', verificationType);
      if (Platform.OS === 'web' && pickedDoc.file) {
        formData.append('document', pickedDoc.file, pickedDoc.name);
      } else {
        formData.append('document', {
          uri: pickedDoc.uri,
          name: pickedDoc.name,
          type: pickedDoc.mimeType,
        } as any);
      }

      const response = await fetch(`${API_BASE_URL}/patient-verifications`, {
        method: 'POST',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        body: formData,
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const validationMessage =
          data?.errors && typeof data.errors === 'object'
            ? Object.values(data.errors).flat().filter(Boolean).join(' ')
            : '';
        setError(validationMessage || data?.message || 'Unable to submit verification request.');
        return;
      }

      setSuccess('Verification request submitted.');
      setVerificationDoc(null);
      setVerificationType('none');
      setTypeMenuOpen(false);

      // Refresh list
      const refreshed = await fetch(`${API_BASE_URL}/patient-verifications?per_page=10`, {
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const refreshedData = await refreshed.json().catch(() => ({}));
      setVerificationItems(Array.isArray(refreshedData?.data) ? refreshedData.data : []);

      const nextUser = {
        ...(globalThis as any)?.currentUser,
        has_pending_verification: true,
      };
      await persistCurrentUser(nextUser);

      if (isOnboarding) {
        router.replace('/screenviews/aut-landing/pending-approval' as any);
      }
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      
      <ScrollView style={styles.pageScroll} contentContainerStyle={styles.pageScrollContent} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <View style={styles.circleTopRight} />
          <View style={styles.circleBottomLeft} />
          <View style={styles.circleMidLeft} />
          
          <View style={styles.headerRow}>
            
            <View>
              <View style={styles.eyebrowRow}>
                <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
                <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
              </View>
              <Text style={styles.headerTitle}>Verification</Text>
                    <Text style={styles.headerGreeting}>
                      {isOnboarding ? 'Step 3 of 3 · Verify your identity and optionally request a patient type.' : 'Verify your identity or request a patient type.'}
                    </Text>
            </View>
             <Pressable
                          style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.85 }]}
                           onPress={() => router.navigate(isOnboarding ? '/screenviews/medical-bg' : '/screenviews/profile' as any)}
                        >
                          <Text style={styles.headerBtnText}>Back</Text>
                        </Pressable>
          </View>
          
        </View>

        <View style={styles.contentSurface}>
          
          {latestRejected && (
            <AnimatedCard delay={50} style={styles.remarksCard}>
              <View style={styles.remarksHeader}>
                <Ionicons name="warning-outline" size={20} color={T.red700} />
                <Text style={styles.remarksTitle}>Verification Remarks</Text>
              </View>
              <Text style={styles.remarksText}>
                {latestRejected.remarks || "Your previous request was rejected. Please review your documents and try again."}
              </Text>
            </AnimatedCard>
          )}

          {error ? <Text style={styles.inlineError}>{error}</Text> : null}
          {success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}

          <AnimatedCard delay={100} style={styles.sectionCard}>
            <View style={styles.sectionHeader}>
              <View style={styles.sectionBadge}>
                <Text style={styles.sectionBadgeText}>Step 1</Text>
              </View>
              <Text style={styles.sectionTitle}>Select Patient Type</Text>
            </View>
            <View style={styles.sectionBody}>
              <Text style={styles.fieldHint}>`None` means identity verification only. A document is still required.</Text>
              <Pressable
                onPress={() => setTypeMenuOpen((current) => !current)}
                style={({ pressed }) => [
                  styles.dropdownTrigger,
                  typeMenuOpen && styles.dropdownTriggerActive,
                  pressed && { opacity: 0.9 },
                ]}
              >
                <Text style={styles.dropdownTriggerText}>
                  {verificationType === 'none'
                    ? 'None'
                    : verificationType === 'pwd'
                      ? 'PWD'
                      : verificationType === 'senior'
                        ? 'Senior'
                        : 'Pregnant'}
                </Text>
                <Ionicons name={typeMenuOpen ? 'chevron-up-outline' : 'chevron-down-outline'} size={18} color={T.slate600} />
              </Pressable>
              {typeMenuOpen ? (
                <View style={styles.dropdownMenu}>
                  {(['none', 'pwd', 'pregnant', 'senior'] as const).map((t) => (
                    <Pressable
                      key={t}
                      onPress={() => {
                        setVerificationType(t);
                        setTypeMenuOpen(false);
                      }}
                      style={({ pressed }) => [
                        styles.dropdownItem,
                        verificationType === t && styles.dropdownItemActive,
                        pressed && { opacity: 0.9 },
                      ]}
                    >
                      <Text style={[styles.dropdownItemText, verificationType === t && styles.dropdownItemTextActive]}>
                        {t === 'none' ? 'None' : t === 'pwd' ? 'PWD' : t === 'senior' ? 'Senior' : 'Pregnant'}
                      </Text>
                      {verificationType === t ? <Ionicons name="checkmark-outline" size={18} color={T.cyan700} /> : null}
                    </Pressable>
                  ))}
                </View>
              ) : null}
            </View>
          </AnimatedCard>

          <AnimatedCard delay={150} style={styles.sectionCard}>
            <View style={styles.sectionHeader}>
              <View style={styles.sectionBadge}>
                <Text style={styles.sectionBadgeText}>Step 2</Text>
              </View>
              <Text style={styles.sectionTitle}>Upload Verification Document</Text>
            </View>
            <View style={styles.sectionBody}>
              <Text style={styles.uploadHint}>
                Upload a clear ID or supporting document. This is required even when patient type is set to `None`.
              </Text>
              <Pressable
                onPress={handlePickDoc}
                style={({ pressed }) => [
                  styles.uploadBox,
                  verificationDoc && styles.uploadBoxActive,
                  pressed && { opacity: 0.85 }
                ]}
              >
                <Ionicons
                  name={verificationDoc ? 'document-attach' : 'cloud-upload-outline'}
                  size={32}
                  color={verificationDoc ? T.cyan700 : T.slate400}
                />
                <Text style={[styles.uploadBoxText, verificationDoc && styles.uploadBoxTextActive]}>
                  {verificationDoc ? verificationDoc.name : 'Tap to select document'}
                </Text>
                <Text style={styles.uploadBoxSub}>Supports JPG, JPEG, PNG, PDF, DOC, DOCX</Text>
              </Pressable>
            </View>
          </AnimatedCard>

          <AnimatedCard delay={200} style={styles.actionSection}>
            <Pressable
              onPress={handleSubmit}
              disabled={submitting || loading || hasPendingVerification}
              style={({ pressed }) => [
                styles.submitBtn,
                (submitting || loading || hasPendingVerification) && styles.submitBtnDisabled,
                pressed && { opacity: 0.85 },
              ]}
            >
              {submitting ? (
                <ActivityIndicator color={T.white} size="small" />
              ) : (
                <>
                  <Text style={styles.submitBtnText}>
                    {hasPendingVerification ? 'Pending verification' : isOnboarding ? 'Submit' : 'Submit verification'}
                  </Text>
                  <Ionicons
                    name={hasPendingVerification ? 'hourglass-outline' : 'send'}
                    size={18}
                    color={T.white}
                  />
                </>
              )}
            </Pressable>
            {hasPendingVerification ? (
              <Text style={styles.pendingNotice}>Please wait for admin/Staff review.</Text>
            ) : null}
          </AnimatedCard>

          <AnimatedCard delay={250} style={styles.sectionCard}>
            <View style={styles.sectionHeader}>
              <View style={styles.sectionBadge}>
                <Text style={styles.sectionBadgeText}>History</Text>
              </View>
              <Text style={styles.sectionTitle}>Recent Requests</Text>
            </View>
            <View style={styles.sectionBody}>
              {loading ? (
                <ActivityIndicator style={{ margin: 20 }} color={T.cyan700} />
              ) : verificationItems.length === 0 ? (
                <Text style={styles.emptyText}>No verification requests found.</Text>
              ) : (
                verificationItems.map((v, i) => (
                  <View key={v.verification_id} style={[styles.historyRow, i === verificationItems.length - 1 && { borderBottomWidth: 0 }]}>
                    <View style={styles.historyIcon}>
                      <Ionicons 
                        name={v.status === 'approved' ? 'checkmark-circle' : v.status === 'rejected' ? 'close-circle' : 'time'} 
                        size={20} 
                        color={v.status === 'approved' ? T.green700 : v.status === 'rejected' ? T.red700 : T.amber700} 
                      />
                    </View>
                    <View style={styles.historyMain}>
                      <Text style={styles.historyTitle}>
                        {v.type === 'none' ? 'None' : v.type === 'pwd' ? 'PWD' : v.type === 'senior' ? 'Senior Citizen' : 'Pregnant'}
                      </Text>
                      <Text style={styles.historyStatus}>{v.status.toUpperCase()}</Text>
                    </View>
                    <Text style={styles.historyDate}>
                      {v.verified_at ? new Date(v.verified_at).toLocaleDateString() : 'Pending'}
                    </Text>
                  </View>
                ))
              )}
            </View>
          </AnimatedCard>
        </View>
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
    paddingBottom: 21.5,
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
    backgroundColor: 'rgba(255,255,255,0.07)',
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 15,
    zIndex: 1,
  },
  headerBtn: {
  position: 'absolute', // Pulls it out of the layout flow
  top: 0.1,              // Distance from the top
  right: 3,            // Distance from the right
  
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
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    alignItems: 'center',
    justifyContent: 'center',
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
  },
  headerGreeting: {
    fontSize: 12,
    color: 'rgba(255,255,255,0.75)',
    marginTop: 2,
    fontWeight: '400',
  },
  pageScroll: { flex: 1, backgroundColor: 'rgba(255,255,255,0.07)' },
  pageScrollContent: { flexGrow: 1 },
  contentSurface: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    paddingTop: 20,
    paddingHorizontal: 16,
    paddingBottom: 32,
    gap: 16,
  },
  remarksCard: {
    backgroundColor: T.red100,
    borderRadius: 18,
    padding: 16,
    borderWidth: 1,
    borderColor: 'rgba(239,68,68,0.2)',
  },
  remarksHeader: { flexDirection: 'row', alignItems: 'center', gap: 8, marginBottom: 6 },
  remarksTitle: { fontSize: 14, fontWeight: '700', color: T.red700 },
  remarksText: { fontSize: 12, color: T.red700, lineHeight: 18, opacity: 0.8 },
  inlineError: { color: T.red700, fontSize: 12, textAlign: 'center' },
  inlineSuccess: { color: T.green700, fontSize: 12, textAlign: 'center' },
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
    flexDirection: 'row',
    alignItems: 'center',
    gap: 8,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
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
    textTransform: 'uppercase',
  },
  sectionTitle: { fontSize: 14, fontWeight: '700', color: T.slate800 },
  sectionBody: { padding: 16 },
  fieldHint: { fontSize: 12, color: T.slate500, marginBottom: 12, lineHeight: 18 },
  dropdownTrigger: {
    minHeight: 48,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    paddingHorizontal: 14,
    paddingVertical: 12,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
  },
  dropdownTriggerActive: {
    borderColor: 'rgba(6,182,212,0.3)',
    backgroundColor: 'rgba(6,182,212,0.08)',
  },
  dropdownTriggerText: { fontSize: 13, fontWeight: '600', color: T.slate700, flex: 1 },
  dropdownMenu: {
    marginTop: 8,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    overflow: 'hidden',
  },
  dropdownItem: {
    minHeight: 44,
    paddingHorizontal: 14,
    paddingVertical: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  dropdownItemActive: {
    backgroundColor: 'rgba(6,182,212,0.08)',
  },
  dropdownItemText: { fontSize: 13, color: T.slate700, flex: 1 },
  dropdownItemTextActive: { color: T.cyan700, fontWeight: '700' },
  uploadHint: { fontSize: 12, color: T.slate500, marginBottom: 12, lineHeight: 18 },
  uploadBox: {
    height: 140,
    borderRadius: 18,
    borderWidth: 2,
    borderStyle: 'dashed',
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    alignItems: 'center',
    justifyContent: 'center',
    gap: 8,
  },
  uploadBoxActive: {
    borderColor: T.cyan500,
    backgroundColor: 'rgba(6,182,212,0.04)',
    borderStyle: 'solid',
  },
  uploadBoxText: { fontSize: 13, fontWeight: '600', color: T.slate600, textAlign: 'center', paddingHorizontal: 20 },
  uploadBoxTextActive: { color: T.cyan700 },
  uploadBoxSub: { fontSize: 10, color: T.slate400 },
  actionSection: { marginTop: 4 },
  submitBtn: {
    backgroundColor: T.cyan700,
    borderRadius: 18,
    paddingVertical: 16,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'center',
    gap: 10,
    shadowColor: T.cyan700,
    shadowOffset: { width: 0, height: 4 },
    shadowOpacity: 0.2,
    shadowRadius: 8,
    elevation: 4,
  },
  submitBtnDisabled: {
    backgroundColor: T.slate400,
    shadowOpacity: 0,
    elevation: 0,
  },
  submitBtnText: { color: T.white, fontSize: 15, fontWeight: '700' },
  pendingNotice: {
    marginTop: 10,
    textAlign: 'center',
    fontSize: 12,
    color: T.slate500,
  },
  emptyText: { textAlign: 'center', color: T.slate400, fontSize: 12, paddingVertical: 20 },
  historyRow: {
    flexDirection: 'row',
    alignItems: 'center',
    paddingVertical: 12,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
    gap: 12,
  },
  historyIcon: {
    width: 36,
    height: 36,
    borderRadius: 10,
    backgroundColor: T.slate50,
    alignItems: 'center',
    justifyContent: 'center',
  },
  historyMain: { flex: 1 },
  historyTitle: { fontSize: 13, fontWeight: '700', color: T.slate800 },
  historyStatus: { fontSize: 10, fontWeight: '600', color: T.slate500, marginTop: 1 },
  historyDate: { fontSize: 11, color: T.slate400 },
});
