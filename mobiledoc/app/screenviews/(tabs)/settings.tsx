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
  Modal,
} from 'react-native';
import { useRouter } from 'expo-router';
// @ts-ignore
import * as DocumentPicker from 'expo-document-picker';
import { clearPersistedAuthSession, persistCurrentUser } from '@/lib/auth-storage';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
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
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type CurrentUser = {
  user_id: number;
  email: string | null;
  account_activated: boolean;
  is_first_login: boolean;
};

type VerificationRequest = {
  verification_id: number;
  type: 'senior' | 'pwd' | 'pregnant';
  status: 'pending' | 'approved' | 'rejected';
  document_path: string | null;
  remarks: string | null;
  verified_at: string | null;
};

type PickedDoc = {
  uri: string;
  name: string;
  mimeType: string;
};

export default function PatientSettingsScreen() {
  const router = useRouter();
  const [user, setUser] = useState<CurrentUser | null>((globalThis as any)?.currentUser ?? null);
  const [loading, setLoading] = useState(false);
  const [savingEmail, setSavingEmail] = useState(false);
  const [savingPassword, setSavingPassword] = useState(false);
  const [loadingVerification, setLoadingVerification] = useState(false);
  const [submittingVerification, setSubmittingVerification] = useState(false);
  const [email, setEmail] = useState('');
  const [newPassword, setNewPassword] = useState('');
  const [confirmPassword, setConfirmPassword] = useState('');
  const [verificationType, setVerificationType] = useState<'senior' | 'pwd' | 'pregnant'>('senior');
  const [verificationDoc, setVerificationDoc] = useState<PickedDoc | null>(null);
  const [verificationItems, setVerificationItems] = useState<VerificationRequest[]>([]);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [logoutOpen, setLogoutOpen] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);

  const accountStatus = useMemo(() => {
    if (!user) return { label: '—', color: T.slate700, bg: T.slate50, border: T.slate200 };
    if (user.account_activated) return { label: 'Activated', color: T.green700, bg: T.green100, border: 'rgba(34,197,94,0.25)' };
    return { label: 'Not activated', color: T.red700, bg: T.red100, border: 'rgba(239,68,68,0.25)' };
  }, [user]);

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

        const response = await fetch(`${API_BASE_URL}/user`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });

        const data = await response.json().catch(() => ({}));
        if (!response.ok) {
          const message =
            typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to load account.';
          setError(message);
          return;
        }

        const nextUser: CurrentUser = {
          user_id: Number(data?.user_id),
          email: data?.email != null ? String(data.email) : null,
          account_activated: !!data?.account_activated,
          is_first_login: !!data?.is_first_login,
        };

        if (!cancelled) {
          setUser(nextUser);
          await persistCurrentUser(nextUser);
          setEmail(nextUser.email ?? '');
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

    async function loadVerifications() {
      setLoadingVerification(true);
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
        if (!cancelled) setLoadingVerification(false);
      }
    }

    loadVerifications();
    return () => {
      cancelled = true;
    };
  }, []);

  async function handlePickVerificationDoc() {
    setError('');
    setSuccess('');
    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*', 'application/pdf'],
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
      });
    } catch {
      setError('Unable to pick a document on this device.');
    }
  }

  async function handleSubmitVerification() {
    if (!verificationDoc) {
      setError('Please upload a document first.');
      return;
    }

    setError('');
    setSuccess('');
    setSubmittingVerification(true);

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const formData = new FormData();
      formData.append('type', verificationType);
      formData.append('document', { uri: verificationDoc.uri, name: verificationDoc.name, type: verificationDoc.mimeType } as any);

      const response = await fetch(`${API_BASE_URL}/patient-verifications`, {
        method: 'POST',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        body: formData,
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message =
          typeof (data as any)?.message === 'string' && (data as any).message.length > 0
            ? (data as any).message
            : 'Unable to submit verification request.';
        setError(message);
        return;
      }

      setSuccess('Verification request submitted.');
      setVerificationDoc(null);

      const refreshed = await fetch(`${API_BASE_URL}/patient-verifications?per_page=10`, {
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const refreshedData = await refreshed.json().catch(() => ({}));
      const list: VerificationRequest[] = Array.isArray((refreshedData as any)?.data) ? (refreshedData as any).data : [];
      setVerificationItems(list);
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSubmittingVerification(false);
    }
  }

  async function handleSaveEmail() {
    const trimmed = email.trim();
    if (!trimmed) {
      setError('Please enter an email.');
      return;
    }
    if (!user?.user_id) {
      setError('Please log in again.');
      return;
    }

    setError('');
    setSuccess('');
    setSavingEmail(true);

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const response = await fetch(`${API_BASE_URL}/users/${user.user_id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ email: trimmed }),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message =
          typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to update email.';
        setError(message);
        return;
      }

      const nextUser: CurrentUser = {
        user_id: Number(data?.user_id),
        email: data?.email != null ? String(data.email) : null,
        account_activated: !!data?.account_activated,
        is_first_login: !!data?.is_first_login,
      };

      setUser(nextUser);
      await persistCurrentUser(nextUser);
      setSuccess('Email updated.');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSavingEmail(false);
    }
  }

  async function handleChangePassword() {
    if (!newPassword || !confirmPassword) {
      setError('Please fill in all password fields.');
      return;
    }
    if (newPassword.length < 8) {
      setError('Password must be at least 8 characters.');
      return;
    }
    if (newPassword !== confirmPassword) {
      setError('Passwords do not match.');
      return;
    }
    if (!user?.user_id) {
      setError('Please log in again.');
      return;
    }

    setError('');
    setSuccess('');
    setSavingPassword(true);

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const response = await fetch(`${API_BASE_URL}/users/${user.user_id}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({ password: newPassword }),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const message =
          typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to update password.';
        setError(message);
        return;
      }

      setNewPassword('');
      setConfirmPassword('');
      setSuccess('Password updated.');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSavingPassword(false);
    }
  }

  async function performLogout() {
    setLoggingOut(true);
    setError('');
    setSuccess('');
    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (token) {
        await fetch(`${API_BASE_URL}/logout`, {
          method: 'POST',
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        }).catch(() => null);
      }
    } finally {
      await clearPersistedAuthSession();
      setUser(null);
      setLogoutOpen(false);
      setLoggingOut(false);
      router.replace('/screenviews/aut-landing/login-screen' as any);
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
            <Text style={styles.headerTitle}>Settings</Text>
            <Text style={styles.headerSub}>Manage your account and security.</Text>
          </View>
        </View>
      </View>

      <ScrollView style={styles.scroll} contentContainerStyle={styles.scrollContent} showsVerticalScrollIndicator={false}>
        {error ? <Text style={styles.inlineError}>{error}</Text> : null}
        {success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Health</Text>
            </View>
            <Text style={styles.cardTitle}>Medical background</Text>
            <Text style={styles.cardSubtitle}>Allergies and conditions used to keep consultations safe.</Text>
          </View>
          <View style={styles.cardBody}>
            <Pressable
              onPress={() => router.push('/screenviews/medical-bg' as any)}
              style={({ pressed }) => [styles.primaryButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.primaryButtonText}>Manage medical background</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Account</Text>
            </View>
            <Text style={styles.cardTitle}>Account details</Text>
            <Text style={styles.cardSubtitle}>Your email and activation status.</Text>
          </View>

          <View style={styles.cardBody}>
            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Email</Text>
              <Text style={styles.infoValue}>{user?.email ?? '—'}</Text>
            </View>

            <View style={styles.infoRow}>
              <Text style={styles.infoLabel}>Status</Text>
              <View style={[styles.statusPill, { backgroundColor: accountStatus.bg, borderColor: accountStatus.border }]}>
                <Text style={[styles.statusPillText, { color: accountStatus.color }]}>{accountStatus.label}</Text>
              </View>
            </View>

            {!user?.account_activated ? (
              <View style={styles.formBlock}>
                <Text style={styles.label}>Add / Update Email</Text>
                <TextInput
                  value={email}
                  onChangeText={setEmail}
                  placeholder="you@example.com"
                  placeholderTextColor="#9ca3af"
                  autoCapitalize="none"
                  keyboardType="email-address"
                  style={styles.input}
                />
                <Pressable
                  onPress={handleSaveEmail}
                  disabled={savingEmail || loading}
                  style={({ pressed }) => [
                    styles.primaryButton,
                    (savingEmail || loading) && { opacity: 0.6 },
                    pressed && { opacity: 0.85 },
                  ]}
                >
                  <Text style={styles.primaryButtonText}>{savingEmail ? 'Saving...' : 'Save Email'}</Text>
                </Pressable>
              </View>
            ) : null}
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Verification</Text>
            </View>
            <Text style={styles.cardTitle}>Patient verification</Text>
            <Text style={styles.cardSubtitle}>Submit PWD, Pregnant, or Senior proof for clinic records.</Text>
          </View>
          <View style={styles.cardBody}>
            <Text style={styles.label}>Type</Text>
            <View style={styles.chipRow}>
              {(['senior', 'pwd', 'pregnant'] as const).map((t) => (
                <Pressable
                  key={t}
                  onPress={() => setVerificationType(t)}
                  style={({ pressed }) => [
                    styles.chip,
                    verificationType === t && styles.chipActive,
                    pressed && { opacity: 0.85 },
                  ]}
                >
                  <Text style={[styles.chipText, verificationType === t && styles.chipTextActive]}>
                    {t === 'pwd' ? 'PWD' : t === 'senior' ? 'Senior' : 'Pregnant'}
                  </Text>
                </Pressable>
              ))}
            </View>

            <Pressable
              onPress={handlePickVerificationDoc}
              style={({ pressed }) => [styles.outlineButton, pressed && { opacity: 0.85 }]}
            >
              <Text style={styles.outlineButtonText}>
                {verificationDoc ? `Document: ${verificationDoc.name}` : 'Upload document (JPG/PNG/PDF)'}
              </Text>
            </Pressable>

            <Pressable
              onPress={handleSubmitVerification}
              disabled={submittingVerification || loadingVerification}
              style={({ pressed }) => [
                styles.primaryButton,
                (submittingVerification || loadingVerification) && { opacity: 0.6 },
                pressed && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.primaryButtonText}>{submittingVerification ? 'Submitting...' : 'Submit verification request'}</Text>
            </Pressable>

            <View style={styles.divider} />
            <Text style={styles.label}>Recent requests</Text>
            {loadingVerification ? <Text style={styles.mutedText}>Loading…</Text> : null}
            {!loadingVerification && verificationItems.length === 0 ? (
              <Text style={styles.mutedText}>No verification requests yet.</Text>
            ) : null}
            {verificationItems.slice(0, 3).map((v) => (
              <View key={String(v.verification_id)} style={styles.verifRow}>
                <Text style={styles.verifTitle}>
                  {(v.type === 'pwd' ? 'PWD' : v.type === 'senior' ? 'Senior' : 'Pregnant') + ' · ' + v.status.toUpperCase()}
                </Text>
                {v.remarks ? <Text style={styles.verifSub}>{v.remarks}</Text> : null}
              </View>
            ))}
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Security</Text>
            </View>
            <Text style={styles.cardTitle}>Change password</Text>
            <Text style={styles.cardSubtitle}>Use a strong password (min 8 characters).</Text>
          </View>

          <View style={styles.cardBody}>
            <Text style={styles.label}>New password</Text>
            <TextInput
              value={newPassword}
              onChangeText={setNewPassword}
              placeholder="Enter a strong password"
              placeholderTextColor="#9ca3af"
              secureTextEntry
              style={styles.input}
            />

            <Text style={[styles.label, { marginTop: 12 }]}>Confirm new password</Text>
            <TextInput
              value={confirmPassword}
              onChangeText={setConfirmPassword}
              placeholder="Re-enter your password"
              placeholderTextColor="#9ca3af"
              secureTextEntry
              style={styles.input}
            />

            <Pressable
              onPress={handleChangePassword}
              disabled={savingPassword || loading}
              style={({ pressed }) => [
                styles.primaryButton,
                (savingPassword || loading) && { opacity: 0.6 },
                pressed && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.primaryButtonText}>{savingPassword ? 'Saving...' : 'Save Password'}</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Session</Text>
            </View>
            <Text style={styles.cardTitle}>Log out</Text>
            <Text style={styles.cardSubtitle}>Sign out from this device.</Text>
          </View>
          <View style={styles.cardBody}>
            <Pressable onPress={() => setLogoutOpen(true)} style={({ pressed }) => [styles.dangerButton, pressed && { opacity: 0.85 }]}>
              <Text style={styles.dangerButtonText}>Log out</Text>
            </Pressable>
          </View>
        </View>

        <Text style={styles.footerNote}>
          {loading ? 'Loading account…' : 'Need help? Contact the clinic front desk.'}
        </Text>
      </ScrollView>

      <Modal visible={logoutOpen} transparent animationType="fade" onRequestClose={() => setLogoutOpen(false)}>
        <View style={styles.modalBackdrop}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Log out</Text>
            <Text style={styles.modalText}>Are you sure you want to log out?</Text>
            <View style={styles.modalActions}>
              <Pressable
                onPress={() => setLogoutOpen(false)}
                disabled={loggingOut}
                style={({ pressed }) => [styles.modalBtn, pressed && { opacity: 0.85 }, loggingOut && { opacity: 0.6 }]}
              >
                <Text style={styles.modalBtnText}>Cancel</Text>
              </Pressable>
              <Pressable
                onPress={performLogout}
                disabled={loggingOut}
                style={({ pressed }) => [styles.modalBtnDanger, pressed && { opacity: 0.85 }, loggingOut && { opacity: 0.6 }]}
              >
                <Text style={styles.modalBtnDangerText}>{loggingOut ? 'Logging out…' : 'Log out'}</Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>
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
  infoRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingVertical: 10,
    borderBottomWidth: StyleSheet.hairlineWidth,
    borderBottomColor: T.slate100,
  },
  infoLabel: {
    fontSize: 12,
    color: T.slate500,
    fontWeight: '600',
  },
  infoValue: {
    fontSize: 12,
    color: T.slate800,
    fontWeight: '600',
  },
  statusPill: {
    paddingHorizontal: 10,
    paddingVertical: 7,
    borderRadius: 999,
    borderWidth: 1,
  },
  statusPillText: {
    fontSize: 11,
    fontWeight: '800',
  },
  formBlock: {
    marginTop: 12,
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
  chipRow: {
    flexDirection: 'row',
    flexWrap: 'wrap',
    gap: 8,
    marginBottom: 12,
  },
  chip: {
    borderRadius: 999,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    paddingHorizontal: 12,
    paddingVertical: 8,
  },
  chipActive: {
    borderColor: 'rgba(34,197,94,0.25)',
    backgroundColor: T.green100,
  },
  chipText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.slate700,
  },
  chipTextActive: {
    color: T.green700,
  },
  outlineButton: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingVertical: 10,
    paddingHorizontal: 12,
  },
  outlineButtonText: {
    fontSize: 12,
    fontWeight: '700',
    color: T.slate700,
  },
  divider: {
    height: 1,
    backgroundColor: T.slate100,
    marginTop: 14,
    marginBottom: 12,
  },
  mutedText: {
    fontSize: 12,
    color: T.slate500,
  },
  verifRow: {
    marginTop: 10,
    paddingTop: 10,
    borderTopWidth: StyleSheet.hairlineWidth,
    borderTopColor: T.slate100,
  },
  verifTitle: {
    fontSize: 12,
    fontWeight: '800',
    color: T.slate800,
  },
  verifSub: {
    fontSize: 11,
    color: T.slate500,
    marginTop: 4,
  },
  footerNote: {
    marginTop: 6,
    fontSize: 11,
    color: T.slate400,
  },
  dangerButton: {
    backgroundColor: '#fee2e2',
    borderWidth: 1,
    borderColor: 'rgba(185,28,28,0.25)',
    paddingVertical: 12,
    paddingHorizontal: 14,
    borderRadius: 14,
    alignItems: 'center',
    justifyContent: 'center',
  },
  dangerButtonText: {
    color: T.red700,
    fontSize: 13,
    fontWeight: '800',
  },
  modalBackdrop: {
    flex: 1,
    backgroundColor: 'rgba(15,23,42,0.5)',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 18,
  },
  modalCard: {
    width: '100%',
    maxWidth: 360,
    backgroundColor: T.white,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: T.slate200,
    padding: 16,
  },
  modalTitle: {
    fontSize: 16,
    fontWeight: '800',
    color: T.slate900,
    marginBottom: 6,
  },
  modalText: {
    fontSize: 13,
    color: T.slate600,
    lineHeight: 18,
  },
  modalActions: {
    marginTop: 14,
    flexDirection: 'row',
    gap: 10,
    justifyContent: 'flex-end',
  },
  modalBtn: {
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 12,
    backgroundColor: T.slate100,
    borderWidth: 1,
    borderColor: T.slate200,
  },
  modalBtnText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate700,
  },
  modalBtnDanger: {
    paddingHorizontal: 14,
    paddingVertical: 10,
    borderRadius: 12,
    backgroundColor: T.red700,
    borderWidth: 1,
    borderColor: T.red700,
  },
  modalBtnDangerText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.white,
  },
});
