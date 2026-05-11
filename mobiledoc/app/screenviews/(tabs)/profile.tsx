import React, { useEffect, useMemo, useState } from 'react';
import {
  ActivityIndicator,
  Image,
  Modal,
  Pressable,
  SafeAreaView,
  ScrollView,
  StatusBar,
  StyleSheet,
  Text,
  TextInput,
  View,
} from 'react-native';
import { Ionicons } from '@expo/vector-icons';
import { useRouter } from 'expo-router';
// @ts-ignore
import * as DocumentPicker from 'expo-document-picker';
import { clearPersistedAuthSession, persistCurrentUser } from '@/lib/auth-storage';
import { Platform } from 'react-native';
const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate300: '#cbd5e1',
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

type ProfileUser = {
  user_id: number;
  firstname: string | null;
  middlename: string | null;
  lastname: string | null;
  birthdate: string | null;
  email: string | null;
  sex: string | null;
  contact_number: string | null;
  prof_path_url: string | null;
};

type PickedImage = {
  uri: string;
  name: string;
  mimeType: string;
};

type EditableProfileForm = {
  firstname: string;
  middlename: string;
  lastname: string;
  birthdate: string;
  sex: '' | 'Male' | 'Female';
  contact_number: string;
};

type VerificationType = 'senior' | 'pwd' | 'pregnant';

type VerificationItem = {
  verification_id: number;
  type: VerificationType;
  status: 'pending' | 'approved' | 'rejected';
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

function formatVerificationType(value: VerificationType | ''): string {
  if (value === 'pwd') return 'PWD';
  if (value === 'senior') return 'Senior';
  if (value === 'pregnant') return 'Pregnant';
  return 'Not specified';
}

function formatDateInput(value: Date): string {
  const year = value.getFullYear();
  const month = String(value.getMonth() + 1).padStart(2, '0');
  const day = String(value.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
}

function formatFullName(user: ProfileUser | null): string {
  if (!user) return 'Patient';
  const fullName = [user.firstname, user.middlename, user.lastname]
    .map((part) => normalizeText(part))
    .filter(Boolean)
    .join(' ');
  return fullName || `Patient #${user.user_id}`;
}

function formatDateOnly(value: string | null): string {
  if (!value) return 'Not provided';
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return 'Not provided';
  return formatDateToWords(date);
}

function formatDateToWords(date: Date): string {
  const months = [
    'January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December'
  ];
  const month = months[date.getMonth()];
  const day = date.getDate();
  const year = date.getFullYear();
  return `${month} ${day}, ${year}`;
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

function getInitials(user: ProfileUser | null): string {
  if (!user) return 'PT';
  const initials = [user.firstname, user.lastname]
    .map((part) => normalizeText(part))
    .filter(Boolean)
    .map((part) => part[0]?.toUpperCase() ?? '')
    .join('');
  return initials || 'PT';
}

function mapApiUserToProfileUser(data: any): ProfileUser {
  let birthdate = data?.birthdate != null ? String(data.birthdate) : null;
  if (birthdate && birthdate.includes('T')) {
    birthdate = birthdate.split('T')[0];
  }

  return {
    user_id: Number(data?.user_id),
    firstname: data?.firstname != null ? String(data.firstname) : null,
    middlename: data?.middlename != null ? String(data.middlename) : null,
    lastname: data?.lastname != null ? String(data.lastname) : null,
    birthdate: birthdate,
    email: data?.email != null ? String(data.email) : null,
    sex: data?.sex != null ? String(data.sex) : null,
    contact_number: data?.contact_number != null ? String(data.contact_number) : null,
    prof_path_url: data?.prof_path_url != null ? String(data.prof_path_url) : null,
  };
}

function buildEditableForm(user: ProfileUser | null): EditableProfileForm {
  const normalizedSex = normalizeText(user?.sex).toLowerCase();
  const sexValue: EditableProfileForm['sex'] =
    normalizedSex === 'male' ? 'Male' : normalizedSex === 'female' ? 'Female' : '';

  return {
    firstname: normalizeText(user?.firstname),
    middlename: normalizeText(user?.middlename),
    lastname: normalizeText(user?.lastname),
    birthdate: normalizeText(user?.birthdate),
    sex: sexValue,
    contact_number: normalizeText(user?.contact_number),
  };
}

export default function ProfileScreen() {
  const router = useRouter();
  const [user, setUser] = useState<ProfileUser | null>((globalThis as any)?.currentUser ?? null);
  const [loading, setLoading] = useState(false);
  const [uploadingImage, setUploadingImage] = useState(false);
  const [savingInfo, setSavingInfo] = useState(false);
  const [editingInfo, setEditingInfo] = useState(false);
  const [logoutOpen, setLogoutOpen] = useState(false);
  const [loggingOut, setLoggingOut] = useState(false);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');
  const [form, setForm] = useState<EditableProfileForm>(buildEditableForm((globalThis as any)?.currentUser ?? null));
  const [verificationTypeLabel, setVerificationTypeLabel] = useState('Not specified');

  const profileName = useMemo(() => formatFullName(user), [user]);
  const profileDobLabel = useMemo(() => {
    return `${formatDateOnly(user?.birthdate ?? null)} (${calculateAge(user?.birthdate ?? null)})`;
  }, [user?.birthdate]);

  useEffect(() => {
    let cancelled = false;

    async function loadProfile() {
      setLoading(true);
      setError('');
      setSuccess('');

      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) {
          if (!cancelled) setError('Please log in again.');
          return;
        }

        const response = await fetch(`${API_BASE_URL}/user`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });
        const data = await response.json().catch(() => ({}));

        if (!response.ok) {
          const message = typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to load profile.';
          if (!cancelled) setError(message);
          return;
        }

        const nextUser = mapApiUserToProfileUser(data);

        if (!cancelled) {
          setUser(nextUser);
          setForm(buildEditableForm(nextUser));
          await persistCurrentUser({ ...(globalThis as any)?.currentUser, ...data });
        }
      } catch {
        if (!cancelled) setError('Network error. Please try again.');
      } finally {
        if (!cancelled) setLoading(false);
      }
    }

    void loadProfile();
    return () => {
      cancelled = true;
    };
  }, []);

  useEffect(() => {
    if (!error && !success) return;
    const timeout = setTimeout(() => {
      setError('');
      setSuccess('');
    }, 3500);
    return () => clearTimeout(timeout);
  }, [error, success]);

  useEffect(() => {
    let cancelled = false;

    async function loadVerificationType() {
      try {
        const token = (globalThis as any)?.apiToken as string | undefined;
        if (!token) return;

        const response = await fetch(`${API_BASE_URL}/patient-verifications?per_page=10`, {
          headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        });
        const data = await response.json().catch(() => ({}));
        if (!response.ok) return;

        const list: VerificationItem[] = Array.isArray((data as any)?.data)
          ? (data as any).data
          : Array.isArray(data)
            ? data
            : [];

        const approved = list.find((item) => item?.status === 'approved');
        const latest = approved ?? list[0];
        const nextLabel = formatVerificationType((latest?.type as VerificationType | undefined) ?? '');
        if (!cancelled) setVerificationTypeLabel(nextLabel);
      } catch {
        if (!cancelled) setVerificationTypeLabel('Not specified');
      }
    }

    void loadVerificationType();
    return () => {
      cancelled = true;
    };
  }, []);

  async function handlePickProfileImage() {
    setError('');
    setSuccess('');

    try {
      const result = await DocumentPicker.getDocumentAsync({
        type: ['image/*'],
        copyToCacheDirectory: true,
        multiple: false,
      });

      if (result.canceled) return;
      const asset = result.assets && result.assets.length ? result.assets[0] : null;
      if (!asset) {
        setError('Unable to read selected image.');
        return;
      }

      const pickedImage: PickedImage = {
        uri: asset.uri,
        name: asset.name,
        mimeType: asset.mimeType ?? 'image/jpeg',
      };

      await uploadProfileImage(pickedImage);
    } catch {
      setError('Unable to pick an image on this device.');
    }
  }

  async function uploadProfileImage(image: PickedImage) {
    setUploadingImage(true);
    setError('');
    setSuccess('');

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const formData = new FormData();
      formData.append('prof_path', {
        uri: image.uri,
        name: image.name,
        type: image.mimeType,
      } as any);

      const response = await fetch(`${API_BASE_URL}/users/me/profile-picture`, {
        method: 'POST',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
        body: formData,
      });
      const data = await response.json().catch(() => ({}));

      if (!response.ok) {
        const message =
          typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to update profile image.';
        setError(message);
        return;
      }

      const nextUser = mapApiUserToProfileUser(data);

      setUser(nextUser);
      setForm(buildEditableForm(nextUser));
      await persistCurrentUser({ ...(globalThis as any)?.currentUser, ...data });
      setSuccess('Profile image updated.');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setUploadingImage(false);
    }
  }

  function updateForm<K extends keyof EditableProfileForm>(key: K, value: EditableProfileForm[K]) {
    setForm((current) => ({
      ...current,
      [key]: value,
    }));
  }

  function handleStartEditing() {
    setForm(buildEditableForm(user));
    setEditingInfo(true);
    setError('');
    setSuccess('');
  }

  function handleCancelEditing() {
    setForm(buildEditableForm(user));
    setEditingInfo(false);
    setError('');
  }

  async function handleSavePersonalInfo() {
    if (!user?.user_id) {
      setError('Please log in again.');
      return;
    }

    const normalizedFirstName = normalizePersonName(form.firstname);
    const normalizedMiddleName = normalizePersonName(form.middlename);
    const normalizedLastName = normalizePersonName(form.lastname);
    const trimmedBirthdate = form.birthdate.trim();
    const trimmedContact = form.contact_number.trim();

    if (!normalizedFirstName || !normalizedLastName) {
      setError('First name and last name are required.');
      return;
    }
    if (
      !isValidPersonName(normalizedFirstName) ||
      (normalizedMiddleName !== '' && !isValidPersonName(normalizedMiddleName)) ||
      !isValidPersonName(normalizedLastName)
    ) {
      setError('Name fields must contain letters only (accents allowed), plus hyphens, apostrophes, periods, and middle dots.');
      return;
    }
    if (trimmedBirthdate && !/^\d{4}-\d{2}-\d{2}$/.test(trimmedBirthdate)) {
      setError('Birthdate must use YYYY-MM-DD format.');
      return;
    }
    if (form.sex !== 'Male' && form.sex !== 'Female') {
      setError('Sex must be either Male or Female.');
      return;
    }
    if (trimmedContact && !isValidPHContact11Digits(trimmedContact)) {
      setError('Contact number must be 11 digits and start with 09.');
      return;
    }

    setSavingInfo(true);
    setError('');
    setSuccess('');

    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const payload = {
        firstname: normalizedFirstName || null,
        middlename: normalizedMiddleName || null,
        lastname: normalizedLastName || null,
        birthdate: trimmedBirthdate || null,
        sex: form.sex.trim() || null,
        contact_number: trimmedContact || null,
      };

      const response = await fetch(`${API_BASE_URL}/personal-information/${user.user_id}`, {
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
        const message =
          typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to update personal info.';
        setError(message);
        return;
      }

      const currentGlobalUser = (globalThis as any)?.currentUser ?? {};
      const nextUser = mapApiUserToProfileUser({ ...currentGlobalUser, ...data });
      setUser(nextUser);
      setForm(buildEditableForm(nextUser));
      setEditingInfo(false);
      await persistCurrentUser({ ...currentGlobalUser, ...data });
      setSuccess('Personal info updated.');
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSavingInfo(false);
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
      <ScrollView style={{ flex: 1 }} contentContainerStyle={{ flexGrow: 1 }} showsVerticalScrollIndicator={false}>
        <View style={styles.header}>
          
    <View style={styles.circleTopRight} />
            <View style={styles.circleBottomLeft} />
            <View style={styles.circleMidLeft} />
    
    <View style={styles.eyebrowRow}>
                <View style={[styles.eyebrowDot, { backgroundColor: 'rgba(255,255,255,0.7)' }]} />
                <Text style={[styles.eyebrowText, { color: 'rgba(255,255,255,0.8)' }]}>Patient Portal</Text>
              </View>

          <Text style={styles.headerTitle}>Profile</Text>
          <Text style={styles.subtitle}>View your account details, profile image, and health shortcuts.</Text>
        </View>

        <View style={[styles.scroll, styles.content]}>
        {!editingInfo && error ? <Text style={styles.inlineError}>{error}</Text> : null}
        {!editingInfo && success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}

        <View style={styles.heroCard}>
          <View style={styles.avatarWrap}>
            {user?.prof_path_url ? (
              <Image source={{ uri: user.prof_path_url }} style={styles.avatarImage} resizeMode="cover" />
            ) : (
              <Text style={styles.avatarInitials}>{getInitials(user)}</Text>
            )}
          </View>
          <Text style={styles.profileName}>{profileName}</Text>
          <Text style={styles.profileSubtext}>{user?.email ?? 'No email on file'}</Text>
          <Pressable
            onPress={handlePickProfileImage}
            disabled={uploadingImage || loading}
            style={({ pressed }) => [
              styles.primaryButton,
              (uploadingImage || loading) && { opacity: 0.6 },
              pressed && { opacity: 0.85 },
            ]}
          >
            <Text style={styles.primaryButtonText}>{uploadingImage ? 'Uploading...' : 'Upload Profile Image'}</Text>
          </Pressable>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.iconWrap}>
              <Ionicons name="person-outline" size={20} color={T.cyan700} />
            </View>
            <View style={styles.cardHeaderText}>
              <Text style={styles.cardTitle}>Personal info</Text>
              <Text style={styles.cardText}>View and update the personal details saved on your account.</Text>
            </View>
          </View>

          {loading ? (
            <View style={styles.loadingRow}>
              <ActivityIndicator size="small" color={T.cyan700} />
              <Text style={styles.loadingText}>Loading profile...</Text>
            </View>
          ) : (
            <>
              {editingInfo ? (
                <View style={styles.infoList}>
                  <View style={styles.fieldGroup}>
                    <Text style={styles.infoLabel}>First name</Text>
                    <TextInput
                      value={form.firstname}
                      onChangeText={(value) => updateForm('firstname', value)}
                      placeholder="First name"
                      placeholderTextColor="#9ca3af"
                      style={styles.input}
                    />
                  </View>
                  <View style={styles.fieldGroup}>
                    <Text style={styles.infoLabel}>Middle name</Text>
                    <TextInput
                      value={form.middlename}
                      onChangeText={(value) => updateForm('middlename', value)}
                      placeholder="Middle name"
                      placeholderTextColor="#9ca3af"
                      style={styles.input}
                    />
                  </View>
                  <View style={styles.fieldGroup}>
                    <Text style={styles.infoLabel}>Last name</Text>
                    <TextInput
                      value={form.lastname}
                      onChangeText={(value) => updateForm('lastname', value)}
                      placeholder="Last name"
                      placeholderTextColor="#9ca3af"
                      style={styles.input}
                    />
                  </View>
                  <View style={styles.fieldGroup}>
                    <Text style={styles.infoLabel}>Date of birth</Text>
                    {Platform.OS === 'web' ? (
                      <View style={styles.selectInput}>
                        {React.createElement('input', {
                          type: 'date',
                          value: form.birthdate || '',
                          max: formatDateInput(new Date()),
                          onChange: (event: any) => updateForm('birthdate', event?.target?.value ?? ''),
                          style: {
                            flex: 1,
                            borderWidth: 0,
                            outlineStyle: 'none',
                            backgroundColor: 'transparent',
                            fontSize: 13,
                            color: T.slate800,
                          },
                        })}
                        {/* <Ionicons name="calendar-outline" size={18} color={T.slate600} /> */}
                      </View>
                    ) : (
                      <TextInput
                        value={form.birthdate}
                        onChangeText={(value) => updateForm('birthdate', value)}
                        placeholder="YYYY-MM-DD"
                        placeholderTextColor="#9ca3af"
                        style={styles.input}
                      />
                    )}
                  </View>
                  <View style={styles.fieldGroup}>
                    <Text style={styles.infoLabel}>Sex</Text>
                    <View style={styles.sexOptionsRow}>
                      {(['Male', 'Female'] as const).map((option) => (
                        <Pressable
                          key={option}
                          onPress={() => updateForm('sex', option)}
                          style={({ pressed }) => [
                            styles.sexOptionChip,
                            form.sex === option && styles.sexOptionChipActive,
                            pressed && { opacity: 0.85 },
                          ]}
                        >
                          <Text style={[styles.sexOptionText, form.sex === option && styles.sexOptionTextActive]}>{option}</Text>
                        </Pressable>
                      ))}
                    </View>
                  </View>
                  <View style={styles.fieldGroup}>
                    <Text style={styles.infoLabel}>Contact number</Text>
                    <TextInput
                      value={form.contact_number}
                      onChangeText={(value) => updateForm('contact_number', value.replace(/\D/g, '').slice(0, 11))}
                      placeholder="09XXXXXXXXX"
                      placeholderTextColor="#9ca3af"
                      keyboardType="phone-pad"
                      maxLength={11}
                      style={styles.input}
                    />
                    <Text style={styles.helperText}>Use 11 digits in 09XXXXXXXXX format.</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Type</Text>
                    <Text style={styles.infoValue}>{verificationTypeLabel}</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Email</Text>
                    <Text style={styles.infoValue}>{normalizeText(user?.email) || 'Not provided'}</Text>
                  </View>
                  <View style={styles.actionRow}>
                    <Pressable
                      onPress={handleCancelEditing}
                      disabled={savingInfo}
                      style={({ pressed }) => [styles.secondaryButton, pressed && { opacity: 0.85 }]}
                    >
                      <Text style={styles.secondaryButtonText}>Cancel</Text>
                    </Pressable>
                    <Pressable
                      onPress={handleSavePersonalInfo}
                      disabled={savingInfo}
                      style={({ pressed }) => [
                        styles.primaryActionButton,
                        savingInfo && { opacity: 0.7 },
                        pressed && { opacity: 0.85 },
                      ]}
                    >
                      <Text style={styles.primaryButtonText}>{savingInfo ? 'Saving...' : 'Save changes'}</Text>
                    </Pressable>
                  </View>
                  <Pressable
                    onPress={() => router.push('/screenviews/verify' as any)}
                    style={({ pressed }) => [styles.secondaryWideButton, pressed && { opacity: 0.85 }]}
                  >
                    <Text style={styles.secondaryWideButtonText}>Verify patient type</Text>
                  </Pressable>
                  {error ? <Text style={styles.inlineError}>{error}</Text> : null}
                  {success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}
                </View>
              ) : (
                <View style={styles.infoList}>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Name</Text>
                    <Text style={styles.infoValue}>{profileName}</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Date of birth</Text>
                    <Text style={styles.infoValue}>{profileDobLabel}</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Email</Text>
                    <Text style={styles.infoValue}>{normalizeText(user?.email) || 'Not provided'}</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Sex</Text>
                    <Text style={styles.infoValue}>{normalizeText(user?.sex) || 'Not provided'}</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Contact number</Text>
                    <Text style={styles.infoValue}>{normalizeText(user?.contact_number) || 'Not provided'}</Text>
                  </View>
                  <View style={styles.infoRow}>
                    <Text style={styles.infoLabel}>Type</Text>
                    <Text style={styles.infoValue}>{verificationTypeLabel}</Text>
                  </View>
                  {/* <Pressable
                    onPress={() => router.push('/screenviews/verify' as any)}
                    style={({ pressed }) => [styles.secondaryWideButton, pressed && { opacity: 0.85 }]}
                  >
                    <Text style={styles.secondaryWideButtonText}>Verify patient type</Text>
                  </Pressable> */}
                  <Pressable
                    onPress={handleStartEditing}
                    style={({ pressed }) => [styles.primaryButton, pressed && { opacity: 0.85 }]}
                  >
                    <Text style={styles.primaryButtonText}>Edit personal info</Text>
                  </Pressable>
                </View>
              )}
            </>
          )}
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            {/* <View style={styles.iconWrap}>
              <Ionicons name="medkit-outline" size={20} color={T.cyan700} />
            </View>
            <View style={styles.cardHeaderText}>
              <Text style={styles.cardTitle}>Other tools</Text>
              <Text style={styles.cardText}>Set your medical background or Verify your patient type.</Text>
            </View> */}
          </View>
          <Pressable
            onPress={() => router.push('/screenviews/medical-bg' as any)}
            style={({ pressed }) => [styles.medicalButton, pressed && { opacity: 0.85 }]}
          >
            <Text style={styles.medicalButtonText}>Medical Background</Text>
          </Pressable>
<br />
             <Pressable
                    onPress={() => router.push('/screenviews/verify' as any)}
                    style={({ pressed }) => [styles.verifyButton, pressed && { opacity: 0.85 }]}
                  >
                    <Text style={styles.verifyButtonText}>Verify patient type</Text>
                  </Pressable>
        </View>



        <Pressable
          onPress={() => setLogoutOpen(true)}
          style={({ pressed }) => [styles.logoutButton, pressed && { opacity: 0.85 }]}
        >
          <Text style={styles.logoutButtonText}>Log Out</Text>
        </Pressable>
      </View>
      </ScrollView>

      <Modal visible={logoutOpen} transparent animationType="fade" onRequestClose={() => setLogoutOpen(false)}>
        <View style={styles.modalOverlay}>
          <View style={styles.modalCard}>
            <Text style={styles.modalTitle}>Confirm log out</Text>
            <Text style={styles.modalText}>Are you sure you want to log out?</Text>
            <View style={styles.modalActions}>
              <Pressable
                onPress={() => setLogoutOpen(false)}
                disabled={loggingOut}
                style={({ pressed }) => [styles.modalSecondaryButton, pressed && { opacity: 0.85 }]}
              >
                <Text style={styles.modalSecondaryButtonText}>Cancel</Text>
              </Pressable>
              <Pressable
                onPress={performLogout}
                disabled={loggingOut}
                style={({ pressed }) => [
                  styles.modalDangerButton,
                  loggingOut && { opacity: 0.7 },
                  pressed && { opacity: 0.85 },
                ]}
              >
                <Text style={styles.modalDangerButtonText}>{loggingOut ? 'Logging out...' : 'Log Out'}</Text>
              </Pressable>
            </View>
          </View>
        </View>
      </Modal>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: T.cyan700 },
   header: {
    backgroundColor: T.cyan700,
    paddingHorizontal: 20,
    paddingTop: 10,
    paddingBottom: 30,
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


  content: { padding: 16, paddingBottom: 32, gap: 14 },
  inlineError: {
    backgroundColor: T.red100,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(239,68,68,0.2)',
    color: T.red700,
    padding: 12,
    fontSize: 12,
  },
  inlineSuccess: {
    backgroundColor: T.green100,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(34,197,94,0.2)',
    color: T.green700,
    padding: 12,
    fontSize: 12,
  },
  heroCard: {
    backgroundColor: T.white,
    borderRadius: 22,
    borderWidth: 1,
    borderColor: T.slate200,
    padding: 20,
    alignItems: 'center',
    shadowColor: T.slate900,
    shadowOpacity: 0.05,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 8,
    elevation: 2,
  },
  avatarWrap: {
    width: 104,
    height: 104,
    borderRadius: 52,
    backgroundColor: 'rgba(6,182,212,0.12)',
    alignItems: 'center',
    justifyContent: 'center',
    overflow: 'hidden',
    marginBottom: 14,
  },
  avatarImage: {
    width: '100%',
    height: '100%',
  },
  avatarInitials: {
    fontSize: 28,
    fontWeight: '800',
    color: T.cyan700,
  },
  profileName: {
    fontSize: 20,
    fontWeight: '800',
    color: T.slate900,
    textAlign: 'center',
    marginBottom: 4,
  },
  profileSubtext: {
    fontSize: 13,
    color: T.slate500,
    textAlign: 'center',
    marginBottom: 14,
  },
  card: {
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
  cardHeader: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    marginBottom: 14,
  },
  iconWrap: {
    width: 42,
    height: 42,
    borderRadius: 14,
    backgroundColor: 'rgba(6,182,212,0.1)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  

 eyebrowRow: { flexDirection: 'row', alignItems: 'center', gap: 5, marginBottom: 4 },
  eyebrowDot: { width: 6, height: 6, borderRadius: 3, backgroundColor: T.cyan500 },
  eyebrowText: { fontSize: 9, fontWeight: '700', letterSpacing: 0.9, textTransform: 'uppercase', color: T.cyan600 },




  cardHeaderText: {
    flex: 1,
  },
  cardTitle: {
    fontSize: 16,
    fontWeight: '700',
    color: T.slate800,
    marginBottom: 4,
  },
  cardText: {
    fontSize: 12,
    lineHeight: 18,
    color: T.slate500,
  },
  loadingRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
  },
  loadingText: {
    fontSize: 12,
    color: T.slate600,
  },
  infoList: {
    gap: 10,
  },
  fieldGroup: {
    gap: 6,
  },
  infoRow: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    padding: 12,
  },
  infoLabel: {
    fontSize: 11,
    fontWeight: '700',
    textTransform: 'uppercase',
    color: T.slate500,
    marginBottom: 4,
  },
  infoValue: {
    fontSize: 13,
    lineHeight: 18,
    color: T.slate800,
  },
  input: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingHorizontal: 12,
    paddingVertical: 11,
    fontSize: 13,
    color: T.slate800,
  },
  selectInput: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingHorizontal: 12,
    paddingVertical: 11,
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
  },
  selectInputValue: {
    fontSize: 13,
    color: T.slate800,
  },
  selectInputPlaceholder: {
    fontSize: 13,
    color: '#9ca3af',
  },
  helperText: {
    fontSize: 11,
    color: T.slate500,
  },
  sexOptionsRow: {
    flexDirection: 'row',
    gap: 10,
  },
  sexOptionChip: {
    flex: 1,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingVertical: 10,
    alignItems: 'center',
    justifyContent: 'center',
  },
  sexOptionChipActive: {
    borderColor: 'rgba(8,145,178,0.3)',
    backgroundColor: 'rgba(6,182,212,0.12)',
  },
  sexOptionText: {
    fontSize: 13,
    fontWeight: '600',
    color: T.slate700,
  },
  sexOptionTextActive: {
    color: T.cyan700,
  },
  actionRow: {
    flexDirection: 'row',
    gap: 10,
  },
  secondaryWideButton: {
    width: '100%',
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryWideButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate700,
    textAlign: 'center',
  },
  primaryButton: {
    width: '100%',
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
    textAlign: 'center',
  },
  primaryActionButton: {
    flex: 1,
    borderRadius: 14,
    backgroundColor: T.cyan700,
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryButton: {
    flex: 1,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  secondaryButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate700,
  },
  logoutButton: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(239,68,68,0.22)',
    backgroundColor: T.red100,
    paddingVertical: 13,
    alignItems: 'center',
    justifyContent: 'center',
  },
  logoutButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.red700,
  },

   medicalButton: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(245, 158, 11, 0.22)', // Subtle amber/orange border
    backgroundColor: '#FFFBEB', // Very light amber (like T.orange100)
    paddingVertical: 13,
    alignItems: 'center',
    justifyContent: 'center',
  },
  medicalButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#D97706', // Strong amber/orange (like T.orange700)
  },

  // Verify Patient: Green
  verifyButton: {
    borderRadius: 14,
    borderWidth: 1,
    borderColor: 'rgba(34, 197, 94, 0.22)', // Subtle green border
    backgroundColor: '#F0FDF4', // Very light green (like T.green100)
    paddingVertical: 13,
    alignItems: 'center',
    justifyContent: 'center',
  },
  verifyButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: '#15803D', // Strong green (like T.green700)
  },
  modalOverlay: {
    flex: 1,
    backgroundColor: 'rgba(15,23,42,0.45)',
    alignItems: 'center',
    justifyContent: 'center',
    padding: 20,
  },
  modalCard: {
    width: '100%',
    maxWidth: 360,
    borderRadius: 20,
    backgroundColor: T.white,
    padding: 20,
  },
  modalTitle: {
    fontSize: 18,
    fontWeight: '800',
    color: T.slate900,
    marginBottom: 8,
  },
  modalText: {
    fontSize: 13,
    lineHeight: 19,
    color: T.slate600,
    marginBottom: 18,
  },
  modalActions: {
    flexDirection: 'row',
    gap: 10,
  },
  modalSecondaryButton: {
    flex: 1,
    borderRadius: 14,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.white,
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  modalSecondaryButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.slate700,
  },
  modalDangerButton: {
    flex: 1,
    borderRadius: 14,
    backgroundColor: T.red700,
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  modalDangerButtonText: {
    fontSize: 13,
    fontWeight: '700',
    color: T.white,
  },
});
