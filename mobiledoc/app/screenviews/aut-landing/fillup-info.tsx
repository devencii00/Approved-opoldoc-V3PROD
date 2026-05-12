import React, { useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  View,
  Platform,
} from 'react-native';
import DateTimePicker from '@react-native-community/datetimepicker';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
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
  red100: 'rgba(239,68,68,0.12)',
  red700: '#b91c1c',
  green100: 'rgba(34,197,94,0.12)',
  green700: '#15803d',
};

const API_BASE_URL = (process.env.EXPO_PUBLIC_API_BASE_URL ?? 'http://localhost:8000/api').replace(/\/+$/, '');

type SexValue = '' | 'Male' | 'Female';

type FormState = {
  firstname: string;
  middlename: string;
  lastname: string;
  birthdate: string;
  sex: SexValue;
  address: string;
  contact_number: string;
};

function normalizeText(value: any): string {
  return typeof value === 'string' ? value.trim() : '';
}

function isValidPersonName(value: string): boolean {
  const v = String(value || '').trim();
  if (v === '') {
    return true;
  }

  try {
    return /^[\p{L}\p{M}][\p{L}\p{M}\s.'\-\u00B7]*$/u.test(v);
  } catch {
    return /^[A-Za-z][A-Za-z\s.'-]*$/.test(v);
  }
}

function normalizePersonName(value: string): string {
  let s = String(value || '').trim();
  if (!s) return '';
  s = s.replace(/\s+/g, ' ');
  s = s.replace(/\s*([.'\-\u00B7])\s*/g, '$1');
  return s;
}

function isValidPHContact11Digits(value: string): boolean {
  return /^09\d{9}$/.test(String(value || ''));
}

function formatDateInput(value: Date): string {
  const year = value.getFullYear();
  const month = String(value.getMonth() + 1).padStart(2, '0');
  const day = String(value.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function parseDateInput(value: string): Date {
  if (/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    const [year, month, day] = value.split('-').map(Number);
    return new Date(year, (month || 1) - 1, day || 1);
  }
  return new Date();
}

function formatDateToWords(value: string): string {
  if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) {
    return '';
  }

  const date = parseDateInput(value);
  if (Number.isNaN(date.getTime())) {
    return '';
  }

  return date.toLocaleDateString('en-US', {
    month: 'long',
    day: 'numeric',
    year: 'numeric',
  });
}

function buildInitialForm(user: any): FormState {
  let birthdate = user?.birthdate != null ? String(user.birthdate) : '';
  if (birthdate && birthdate.includes('T')) {
    birthdate = birthdate.split('T')[0];
  }
  const normalizedSex = normalizeText(user?.sex).toLowerCase();
  const sexValue: SexValue = normalizedSex === 'male' ? 'Male' : normalizedSex === 'female' ? 'Female' : '';

  return {
    firstname: normalizeText(user?.firstname),
    middlename: normalizeText(user?.middlename),
    lastname: normalizeText(user?.lastname),
    birthdate: normalizeText(birthdate),
    sex: sexValue,
    address: normalizeText(user?.address),
    contact_number: normalizeText(user?.contact_number),
  };
}

export default function FillupInfoScreen() {
  const router = useRouter();
  const currentUser = (globalThis as any)?.currentUser as any | undefined;
  const userId = Number(currentUser?.user_id || 0);

  const [form, setForm] = useState<FormState>(() => buildInitialForm(currentUser ?? null));
  const [saving, setSaving] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [showBirthdatePicker, setShowBirthdatePicker] = useState(false);
  const [sexMenuOpen, setSexMenuOpen] = useState(false);

  const canContinue = useMemo(() => {
    if (saving) return false;
    if (!normalizeText(form.firstname) || !normalizeText(form.lastname)) return false;
    if (!form.birthdate || !/^\d{4}-\d{2}-\d{2}$/.test(form.birthdate)) return false;
    if (form.sex !== 'Male' && form.sex !== 'Female') return false;
    if (!normalizeText(form.address)) return false;
    if (!normalizeText(form.contact_number) || !isValidPHContact11Digits(form.contact_number)) return false;
    return true;
  }, [form, saving]);

  function update<K extends keyof FormState>(key: K, value: FormState[K]) {
    setForm((prev) => ({ ...prev, [key]: value }));
  }

  async function handleLogout() {
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
      router.replace('/screenviews/aut-landing/login-screen' as any);
    }
  }

  async function handleSaveAndContinue() {
    setError('');
    setSuccess('');

    if (!userId) {
      router.replace('/screenviews/aut-landing/login-screen' as any);
      return;
    }

    const firstname = normalizePersonName(form.firstname);
    const middlename = normalizePersonName(form.middlename);
    const lastname = normalizePersonName(form.lastname);
    const birthdate = normalizeText(form.birthdate);
    const address = normalizeText(form.address);
    const contact = normalizeText(form.contact_number);

    if (!firstname || !lastname) {
      setError('First name and last name are required.');
      return;
    }
    if (
      !isValidPersonName(firstname) ||
      (middlename !== '' && !isValidPersonName(middlename)) ||
      !isValidPersonName(lastname)
    ) {
      setError('Name fields must contain letters only (accents allowed), plus hyphens, apostrophes, periods, and middle dots.');
      return;
    }
    if (!/^\d{4}-\d{2}-\d{2}$/.test(birthdate)) {
      setError('Birthdate must use YYYY-MM-DD format.');
      return;
    }
    if (form.sex !== 'Male' && form.sex !== 'Female') {
      setError('Sex must be either Male or Female.');
      return;
    }
    if (!address) {
      setError('Address is required.');
      return;
    }
    if (!contact || !isValidPHContact11Digits(contact)) {
      setError('Contact number must be 11 digits and start with 09.');
      return;
    }

    const token = (globalThis as any)?.apiToken as string | undefined;
    if (!token) {
      router.replace('/screenviews/aut-landing/login-screen' as any);
      return;
    }

    setSaving(true);
    try {
      const payload = {
        firstname: firstname || null,
        middlename: middlename || null,
        lastname: lastname || null,
        birthdate: birthdate || null,
        sex: form.sex || null,
        address: address || null,
        contact_number: contact || null,
      };

      const response = await fetch(`${API_BASE_URL}/personal-information/${userId}`, {
        method: 'PATCH',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify(payload),
      });

      const data = await response.json().catch(() => ({}));
      if (!response.ok) {
        const validationMessage =
          data?.errors && typeof data.errors === 'object'
            ? Object.values(data.errors).flat().filter(Boolean).join(' ')
            : '';
        const message =
          validationMessage ||
          (typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to save personal info.');
        setError(message);
        return;
      }

      const nextUser = { ...(globalThis as any)?.currentUser, ...data };
      await persistCurrentUser(nextUser);
      setSuccess('Saved.');
      router.push('/screenviews/medical-bg' as any);
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSaving(false);
    }
  }

  return (
    <SafeAreaView style={styles.safe}>
      <StatusBar barStyle="light-content" backgroundColor={T.cyan700} />
      <ScrollView style={{ flex: 1 }} contentContainerStyle={{ flexGrow: 1 }} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          <View style={styles.circleTopRight} />
          <View style={styles.circleBottomLeft} />
          <View style={styles.circleMidLeft} />
          <View style={styles.headerInner}>
            <View style={{ flex: 1 }}>
              <View style={styles.eyebrowRow}>
                <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
                <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
              </View>
              <Text style={styles.headerTitle}>Tell us about yourself</Text>
              <Text style={styles.headerSub}>Step 1 of 3 · Personal information is required on first login.</Text>
            </View>
            <Pressable
              style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.85 }]}
              onPress={handleLogout}
            >
              <Text style={styles.headerBtnText}>Log out</Text>
            </Pressable>
          </View>
        </View>

        <View style={[styles.scroll, styles.scrollContent]}>
          {error ? <Text style={styles.inlineError}>{error}</Text> : null}
          {success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}

          <View style={styles.card}>
            <View style={styles.cardHeader}>
              <Text style={styles.cardTitle}>Tell us about yourself</Text>
              <Text style={styles.cardSubtitle}>These details will be locked after approval (contact number can be updated later).</Text>
            </View>

            <View style={styles.cardBody}>
              <Text style={styles.label}>First name</Text>
              <TextInput
                value={form.firstname}
                onChangeText={(v) => update('firstname', v)}
                placeholder="First name"
                placeholderTextColor="#9ca3af"
                style={styles.input}
              />

              <Text style={[styles.label, { marginTop: 12 }]}>Middle name (optional)</Text>
              <TextInput
                value={form.middlename}
                onChangeText={(v) => update('middlename', v)}
                placeholder="Middle name"
                placeholderTextColor="#9ca3af"
                style={styles.input}
              />

              <Text style={[styles.label, { marginTop: 12 }]}>Last name</Text>
              <TextInput
                value={form.lastname}
                onChangeText={(v) => update('lastname', v)}
                placeholder="Last name"
                placeholderTextColor="#9ca3af"
                style={styles.input}
              />

              <Text style={[styles.label, { marginTop: 12 }]}>Date of birth</Text>
              {Platform.OS === 'web' ? (
                <View style={styles.selectInput}>
                  {React.createElement('input', {
                    type: 'date',
                    value: form.birthdate || '',
                    max: formatDateInput(new Date()),
                    onChange: (event: any) => update('birthdate', event?.target?.value ?? ''),
                    onFocus: () => setSexMenuOpen(false),
                    style: {
                      flex: 1,
                      borderWidth: 0,
                      outlineStyle: 'none',
                      backgroundColor: 'transparent',
                      fontSize: 13,
                      color: T.slate800,
                    },
                  })}
                </View>
              ) : (
                <>
                  <Pressable
                    onPress={() => setShowBirthdatePicker(true)}
                    style={({ pressed }) => [styles.selectInput, pressed && { opacity: 0.85 }]}
                  >
                    <Text style={form.birthdate ? styles.selectInputValue : styles.selectInputPlaceholder}>
                      {form.birthdate || 'YYYY-MM-DD'}
                    </Text>
                    <Ionicons name="calendar-outline" size={18} color={T.slate600} />
                  </Pressable>
                  {showBirthdatePicker ? (
                    <DateTimePicker
                      value={parseDateInput(form.birthdate)}
                      mode="date"
                      maximumDate={new Date()}
                      display={Platform.OS === 'ios' ? 'spinner' : 'default'}
                      onChange={(event, date) => {
                        if (Platform.OS !== 'ios') {
                          setShowBirthdatePicker(false);
                        }
                        if (event.type === 'dismissed' || !date) {
                          return;
                        }
                        update('birthdate', formatDateInput(date));
                        if (Platform.OS === 'ios') {
                          setShowBirthdatePicker(false);
                        }
                      }}
                    />
                  ) : null}
                </>
              )}
              {form.birthdate ? <Text style={styles.helperText}>{formatDateToWords(form.birthdate)}</Text> : null}

              <Text style={[styles.label, { marginTop: 12 }]}>Sex</Text>
              <View style={styles.dropdownWrap}>
                <Pressable
                  onPress={() => setSexMenuOpen((current) => !current)}
                  style={({ pressed }) => [
                    styles.selectInput,
                    sexMenuOpen && styles.selectInputActive,
                    pressed && { opacity: 0.9 },
                  ]}
                >
                  <Text style={form.sex ? styles.selectInputValue : styles.selectInputPlaceholder}>
                    {form.sex || 'Select sex'}
                  </Text>
                  <Ionicons name={sexMenuOpen ? 'chevron-up-outline' : 'chevron-down-outline'} size={18} color={T.slate600} />
                </Pressable>
                {sexMenuOpen ? (
                  <View style={styles.dropdownMenu}>
                    {(['Male', 'Female'] as const).map((option) => (
                      <Pressable
                        key={option}
                        onPress={() => {
                          update('sex', option);
                          setSexMenuOpen(false);
                        }}
                        style={({ pressed }) => [
                          styles.dropdownItem,
                          form.sex === option && styles.dropdownItemActive,
                          pressed && { opacity: 0.9 },
                        ]}
                      >
                        <Text style={[styles.dropdownItemText, form.sex === option && styles.dropdownItemTextActive]}>{option}</Text>
                        {form.sex === option ? <Ionicons name="checkmark-outline" size={18} color={T.cyan700} /> : null}
                      </Pressable>
                    ))}
                  </View>
                ) : null}
              </View>

              <Text style={[styles.label, { marginTop: 12 }]}>Address</Text>
              <TextInput
                value={form.address}
                onChangeText={(v) => update('address', v)}
                onFocus={() => setSexMenuOpen(false)}
                placeholder="Full address"
                placeholderTextColor="#9ca3af"
                style={[styles.input, { height: 92, textAlignVertical: 'top' }]}
                multiline
              />

              <Text style={[styles.label, { marginTop: 12 }]}>Contact number</Text>
              <TextInput
                value={form.contact_number}
                onChangeText={(v) => update('contact_number', v.replace(/\D/g, '').slice(0, 11))}
                onFocus={() => setSexMenuOpen(false)}
                placeholder="09XXXXXXXXX"
                placeholderTextColor="#9ca3af"
                keyboardType="phone-pad"
                maxLength={11}
                style={styles.input}
              />
              <Text style={styles.helperText}>Use 11 digits in 09XXXXXXXXX format.</Text>
              <Text style={styles.noticeText}>Make sure the details you input will match your id/document.</Text>

              <Pressable
                onPress={handleSaveAndContinue}
                disabled={!canContinue}
                style={({ pressed }) => [
                  styles.primaryButton,
                  !canContinue && { opacity: 0.6 },
                  pressed && canContinue && { opacity: 0.85 },
                ]}
              >
                {saving ? <ActivityIndicator color={T.white} size="small" /> : null}
                <Text style={styles.primaryButtonText}>{saving ? 'Saving...' : 'Next'}</Text>
                {!saving ? <Ionicons name="arrow-forward" size={18} color={T.white} /> : null}
              </Pressable>
            </View>
          </View>
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
    paddingBottom: 30,
    position: 'relative',
    overflow: 'hidden',
  },
  headerInner: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 12,
  },
  headerTitle: {
    fontFamily: 'serif',
    fontSize: 30,
    fontWeight: '700',
    color: T.white,
    marginBottom: 2,
    letterSpacing: 0.3,
  },
  headerSub: { fontSize: 12, color: 'rgba(255,255,255,0.75)', fontWeight: '400' },
  headerBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: 'rgba(255,255,255,0.16)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
  },
  headerBtnText: { color: T.white, fontSize: 12, fontWeight: '600' },
  eyebrowRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginBottom: 4 },
  eyebrowDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: T.cyan500 },
  eyebrowText: { fontSize: 9, fontWeight: '700', letterSpacing: 0.9, textTransform: 'uppercase', color: T.cyan600 },
  scroll: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    marginTop: -16,
  },
  scrollContent: { padding: 16, gap: 12, paddingBottom: 28 },
  inlineError: {
    backgroundColor: T.red100,
    borderColor: 'rgba(239,68,68,0.25)',
    borderWidth: 1,
    color: T.red700,
    padding: 10,
    borderRadius: 12,
    fontSize: 12,
  },
  inlineSuccess: {
    backgroundColor: T.green100,
    borderColor: 'rgba(34,197,94,0.25)',
    borderWidth: 1,
    color: T.green700,
    padding: 10,
    borderRadius: 12,
    fontSize: 12,
  },
  card: {
    backgroundColor: T.white,
    borderWidth: 1,
    borderColor: T.slate200,
    borderRadius: 18,
    overflow: 'hidden',
    shadowColor: '#0f172a',
    shadowOpacity: 0.04,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 10,
    elevation: 2,
  },
  cardHeader: { paddingHorizontal: 14, paddingTop: 14, paddingBottom: 10 },
  cardTitle: { fontSize: 16, fontWeight: '700', color: T.slate900, marginBottom: 3 },
  cardSubtitle: { fontSize: 12, color: T.slate500 },
  cardBody: { paddingHorizontal: 14, paddingBottom: 14, gap: 10 },
  label: { fontSize: 11, fontWeight: '600', color: T.slate700 },
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
  helperText: { fontSize: 11, color: T.slate500, marginTop: -4 },
  noticeText: {
    fontSize: 11,
    color: T.slate600,
    lineHeight: 16,
    marginTop: -2,
  },
  dropdownWrap: {
    position: 'relative',
    zIndex: 10,
  },
  selectInput: {
    borderRadius: 12,
    borderWidth: 1,
    borderColor: T.slate200,
    paddingHorizontal: 12,
    paddingVertical: 10,
    backgroundColor: T.white,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
  },
  selectInputActive: {
    borderColor: 'rgba(8,145,178,0.35)',
  },
  selectInputPlaceholder: { fontSize: 13, color: T.slate400 },
  selectInputValue: { fontSize: 13, color: T.slate800 },
  dropdownMenu: {
    marginTop: 8,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    overflow: 'hidden',
    shadowColor: '#0f172a',
    shadowOpacity: 0.06,
    shadowOffset: { width: 0, height: 4 },
    shadowRadius: 10,
    elevation: 3,
  },
  dropdownItem: {
    minHeight: 44,
    paddingHorizontal: 12,
    paddingVertical: 10,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
    borderBottomWidth: 1,
    borderBottomColor: T.slate100,
  },
  dropdownItemActive: {
    backgroundColor: '#ecfeff',
  },
  dropdownItemText: {
    fontSize: 13,
    color: T.slate700,
    flex: 1,
  },
  dropdownItemTextActive: {
    color: T.cyan700,
    fontWeight: '700',
  },
  primaryButton: {
    marginTop: 8,
    backgroundColor: T.cyan700,
    borderRadius: 14,
    paddingVertical: 12,
    paddingHorizontal: 14,
    alignItems: 'center',
    justifyContent: 'center',
    flexDirection: 'row',
    gap: 10,
  },
  primaryButtonText: { color: T.white, fontSize: 13, fontWeight: '700' },
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
});
