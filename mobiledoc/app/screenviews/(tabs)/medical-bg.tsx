import React, { useEffect, useMemo, useState } from 'react';
import {
  SafeAreaView,
  StatusBar,
  View,
  Text,
  StyleSheet,
  ScrollView,
  Pressable,
  TextInput,
} from 'react-native';
import { useRouter } from 'expo-router';

const T = {
  cyan500: '#06b6d4',
  cyan600: '#0891b2',
  cyan700: '#0e7490',
  slate50: '#f8fafc',
  slate100: '#f1f5f9',
  slate200: '#e2e8f0',
  slate400: '#94a3b8',
  slate500: '#64748b',
  slate600: '#556370',
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

type MedicalBackgroundCategory = 'allergy_food' | 'allergy_drug' | 'condition';

type MedicalBackgroundItem = {
  medical_background_id: number;
  category: MedicalBackgroundCategory;
  name: string;
  notes: string | null;
};

function categoryLabel(category: MedicalBackgroundCategory): string {
  if (category === 'allergy_food') return 'Allergy (Food)';
  if (category === 'allergy_drug') return 'Allergy (Drug)';
  return 'Condition';
}

export default function PatientMedicalBackgroundScreen() {
  const router = useRouter();
  const [items, setItems] = useState<MedicalBackgroundItem[]>([]);
  const [loading, setLoading] = useState(false);
  const [saving, setSaving] = useState(false);
  const [deletingId, setDeletingId] = useState<number | null>(null);
  const [error, setError] = useState('');
  const [success, setSuccess] = useState('');

  const [category, setCategory] = useState<MedicalBackgroundCategory>('condition');
  const [name, setName] = useState('');
  const [notes, setNotes] = useState('');

  const canSave = useMemo(() => {
    return name.trim().length > 0 && !saving;
  }, [name, saving]);

  async function load() {
    setLoading(true);
    setError('');
    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const res = await fetch(`${API_BASE_URL}/medical-backgrounds?per_page=100`, {
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const msg = typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to load medical background.';
        setError(msg);
        return;
      }

      const raw = Array.isArray(data?.data) ? data.data : Array.isArray(data) ? data : [];
      const mapped: MedicalBackgroundItem[] = raw
        .map((r: any) => ({
          medical_background_id: Number(r?.medical_background_id),
          category: String(r?.category ?? 'condition') as MedicalBackgroundCategory,
          name: r?.name != null ? String(r.name) : '',
          notes: r?.notes != null ? String(r.notes) : null,
        }))
        .filter((x: MedicalBackgroundItem) => x.medical_background_id > 0);
      setItems(mapped);
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setLoading(false);
    }
  }

  useEffect(() => {
    load();
  }, []);

  async function addEntry() {
    const trimmedName = name.trim();
    const trimmedNotes = notes.trim();
    if (!trimmedName) {
      setError('Please enter a name.');
      return;
    }

    setSaving(true);
    setError('');
    setSuccess('');
    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const res = await fetch(`${API_BASE_URL}/medical-backgrounds`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          Accept: 'application/json',
          Authorization: `Bearer ${token}`,
        },
        body: JSON.stringify({
          category,
          name: trimmedName,
          notes: trimmedNotes.length > 0 ? trimmedNotes : null,
        }),
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const msg = typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to save entry.';
        setError(msg);
        return;
      }

      setName('');
      setNotes('');
      setSuccess('Saved.');
      await load();
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setSaving(false);
    }
  }

  async function deleteEntry(id: number) {
    if (!id) return;
    setDeletingId(id);
    setError('');
    setSuccess('');
    try {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        setError('Please log in again.');
        return;
      }

      const res = await fetch(`${API_BASE_URL}/medical-backgrounds/${id}`, {
        method: 'DELETE',
        headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
      });
      const data = await res.json().catch(() => ({}));
      if (!res.ok) {
        const msg = typeof data?.message === 'string' && data.message.length > 0 ? data.message : 'Unable to delete entry.';
        setError(msg);
        return;
      }
      setSuccess('Deleted.');
      await load();
    } catch {
      setError('Network error. Please try again.');
    } finally {
      setDeletingId(null);
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
              <Text style={styles.headerTitle}>Medical background</Text>
              <Text style={styles.headerSub}>Add allergies and conditions before booking.</Text>
            </View>
            <Pressable onPress={() => router.back()} style={({ pressed }) => [styles.headerBtn, pressed && { opacity: 0.7 }]}>
              <Text style={styles.headerBtnText}>Back</Text>
            </Pressable>
          </View>
        </View>

        <View style={[styles.scroll, styles.scrollContent]}>
        {error ? <Text style={styles.inlineError}>{error}</Text> : null}
        {success ? <Text style={styles.inlineSuccess}>{success}</Text> : null}

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>Add</Text>
            </View>
            <Text style={styles.cardTitle}>New entry</Text>
            <Text style={styles.cardSubtitle}>You can add multiple entries.</Text>
          </View>

          <View style={styles.cardBody}>
            <Text style={styles.label}>Category</Text>
            <View style={styles.categoryRow}>
              {(['allergy_food', 'allergy_drug', 'condition'] as MedicalBackgroundCategory[]).map((c) => (
                <Pressable
                  key={c}
                  onPress={() => setCategory(c)}
                  style={({ pressed }) => [
                    styles.categoryChip,
                    category === c && styles.categoryChipActive,
                    pressed && { opacity: 0.85 },
                  ]}
                >
                  <Text style={[styles.categoryChipText, category === c && styles.categoryChipTextActive]}>
                    {categoryLabel(c)}
                  </Text>
                </Pressable>
              ))}
            </View>

            <Text style={[styles.label, { marginTop: 12 }]}>Name</Text>
            <TextInput
              value={name}
              onChangeText={setName}
              placeholder="e.g. Penicillin, Asthma, Shellfish"
              placeholderTextColor="#9ca3af"
              style={styles.input}
            />

            <Text style={[styles.label, { marginTop: 12 }]}>Notes (optional)</Text>
            <TextInput
              value={notes}
              onChangeText={setNotes}
              placeholder="Additional details"
              placeholderTextColor="#9ca3af"
              style={[styles.input, { height: 84, textAlignVertical: 'top' }]}
              multiline
            />

            <Pressable
              onPress={addEntry}
              disabled={!canSave}
              style={({ pressed }) => [
                styles.primaryButton,
                (!canSave || saving) && { opacity: 0.55 },
                pressed && { opacity: 0.85 },
              ]}
            >
              <Text style={styles.primaryButtonText}>{saving ? 'Saving…' : 'Add entry'}</Text>
            </Pressable>
          </View>
        </View>

        <View style={styles.card}>
          <View style={styles.cardHeader}>
            <View style={styles.eyebrowRow}>
              <View style={styles.eyebrowDot} />
              <Text style={styles.eyebrowText}>List</Text>
            </View>
            <Text style={styles.cardTitle}>Your entries</Text>
            <Text style={styles.cardSubtitle}>
              {loading ? 'Loading…' : items.length === 0 ? 'No entries yet.' : 'Tap Delete to remove an entry.'}
            </Text>
          </View>

          <View style={styles.cardBody}>
            {items.map((it) => (
              <View key={it.medical_background_id} style={styles.itemRow}>
                <View style={{ flex: 1 }}>
                  <Text style={styles.itemTitle}>{it.name}</Text>
                  <Text style={styles.itemSub}>
                    {categoryLabel(it.category)}
                    {it.notes ? ` · ${it.notes}` : ''}
                  </Text>
                </View>
                <Pressable
                  onPress={() => deleteEntry(it.medical_background_id)}
                  disabled={deletingId === it.medical_background_id}
                  style={({ pressed }) => [
                    styles.dangerBtn,
                    deletingId === it.medical_background_id && { opacity: 0.6 },
                    pressed && { opacity: 0.85 },
                  ]}
                >
                  <Text style={styles.dangerBtnText}>{deletingId === it.medical_background_id ? '…' : 'Delete'}</Text>
                </Pressable>
              </View>
            ))}
          </View>
        </View>

        <Pressable
          onPress={() => router.back()}
          style={({ pressed }) => [styles.secondaryButton, pressed && { opacity: 0.85 }]}
        >
          <Text style={styles.secondaryButtonText}>Done</Text>
        </Pressable>
        </View>
      </ScrollView>
    </SafeAreaView>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: T.cyan700 },
  header: { backgroundColor: T.cyan700, paddingHorizontal: 20, paddingTop: 12, paddingBottom: 30 },
  headerInner: { flexDirection: 'row', alignItems: 'flex-start', justifyContent: 'space-between', gap: 12 },
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
  categoryRow: { flexDirection: 'row', flexWrap: 'wrap', gap: 8 },
  categoryChip: {
    paddingHorizontal: 10,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: T.slate50,
    borderWidth: 1,
    borderColor: T.slate200,
  },
  categoryChipActive: { backgroundColor: '#ecfeff', borderColor: 'rgba(8,145,178,0.25)' },
  categoryChipText: { fontSize: 12, fontWeight: '600', color: T.slate700 },
  categoryChipTextActive: { color: T.cyan700 },
  primaryButton: {
    marginTop: 6,
    borderRadius: 14,
    backgroundColor: T.cyan700,
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
  },
  primaryButtonText: { color: T.white, fontSize: 13, fontWeight: '700' },
  itemRow: {
    flexDirection: 'row',
    alignItems: 'center',
    gap: 10,
    borderWidth: 1,
    borderColor: T.slate200,
    backgroundColor: T.slate50,
    padding: 12,
    borderRadius: 14,
  },
  itemTitle: { fontSize: 13, fontWeight: '700', color: T.slate900, marginBottom: 2 },
  itemSub: { fontSize: 12, color: T.slate600 },
  dangerBtn: {
    paddingHorizontal: 12,
    paddingVertical: 8,
    borderRadius: 999,
    backgroundColor: T.red100,
    borderWidth: 1,
    borderColor: 'rgba(239,68,68,0.25)',
  },
  dangerBtnText: { fontSize: 12, fontWeight: '700', color: T.red700 },
  secondaryButton: {
    borderRadius: 14,
    backgroundColor: T.white,
    borderWidth: 1,
    borderColor: T.slate200,
    paddingVertical: 12,
    alignItems: 'center',
    justifyContent: 'center',
    marginTop: 2,
  },
  secondaryButtonText: { color: T.slate800, fontSize: 13, fontWeight: '700' },
});
