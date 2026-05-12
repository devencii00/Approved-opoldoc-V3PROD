import React, { useEffect, useState } from 'react';
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
import { useIsFocused } from '@react-navigation/native';
import { useRouter } from 'expo-router';
import { Ionicons } from '@expo/vector-icons';
import {
  fetchPatientNotifications,
  formatNotificationTimestamp,
  type PatientNotification,
} from '../../../lib/notifications';

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
  slate700: '#334155',
  slate800: '#1e293b',
  white: '#ffffff',
  green700: '#15803d',
  amber700: '#b45309',
  red700: '#b91c1c',
};

function iconForType(type: string): React.ComponentProps<typeof Ionicons>['name'] {
  const normalized = type.trim().toLowerCase();
  if (normalized === 'appointment') return 'calendar-clear-outline';
  if (normalized === 'payment') return 'card-outline';
  return 'shield-checkmark-outline';
}

function iconTintForType(type: string): string {
  const normalized = type.trim().toLowerCase();
  if (normalized === 'appointment') return T.cyan700;
  if (normalized === 'payment') return T.green700;
  return T.amber700;
}

function NotificationCard({ item }: { item: PatientNotification }) {
  const tint = iconTintForType(item.type);

  return (
    <View style={styles.notificationCard}>
      <View style={[styles.notificationIconWrap, { backgroundColor: `${tint}14` }]}>
        <Ionicons name={iconForType(item.type)} size={18} color={tint} />
      </View>
      <View style={styles.notificationContent}>
        <View style={styles.notificationTopRow}>
          <Text style={styles.notificationTitle}>{item.title}</Text>
          {!item.isRead ? <View style={styles.unreadDot} /> : null}
        </View>
        <Text style={styles.notificationBody}>{item.body}</Text>
        <Text style={styles.notificationMeta}>{formatNotificationTimestamp(item.createdAt)}</Text>
      </View>
    </View>
  );
}

export default function NotificationsScreen() {
  const router = useRouter();
  const isFocused = useIsFocused();
  const [notifications, setNotifications] = useState<PatientNotification[]>([]);
  const [loading, setLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    if (!isFocused) return;

    let cancelled = false;

    async function loadNotifications() {
      const token = (globalThis as any)?.apiToken as string | undefined;
      if (!token) {
        if (!cancelled) {
          setError('Please log in again.');
          setNotifications([]);
          setLoading(false);
        }
        return;
      }

      if (!cancelled) {
        setLoading(true);
        setError('');
      }

      try {
        const items = await fetchPatientNotifications(token, 25);
        if (!cancelled) {
          setNotifications(items);
        }
      } catch (err) {
        if (!cancelled) {
          setError(err instanceof Error && err.message ? err.message : 'Unable to load notifications.');
          setNotifications([]);
        }
      } finally {
        if (!cancelled) {
          setLoading(false);
        }
      }
    }

    loadNotifications();
    return () => {
      cancelled = true;
    };
  }, [isFocused]);

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
            <View style={styles.headerTitleWrap}>
              <View style={styles.eyebrowRow}>
                <View style={styles.eyebrowDot} />
                <Text style={styles.eyebrowText}>Patient Portal</Text>
              </View>
              <Text style={styles.headerTitle}>Notifications</Text>
              <Text style={styles.headerSubtitle}>Appointment, queue, payment, and account updates in one place.</Text>
            </View>

            <Pressable style={styles.backBtn} onPress={() => router.back()}>
              <Ionicons name="arrow-back" size={18} color={T.white} />
            </Pressable>
          </View>
        </View>

        <View style={styles.contentSurface}>
          <View style={styles.sectionHeader}>
            <View>
              <Text style={styles.sectionTitle}>Notifications Center</Text>
              <Text style={styles.sectionSubtitle}>Showing the latest updates for your account.</Text>
            </View>
            <View style={styles.sectionBadge}>
              <Text style={styles.sectionBadgeText}>{notifications.length}</Text>
            </View>
          </View>

          {loading ? (
            <View style={styles.stateWrap}>
              <ActivityIndicator size="small" color={T.cyan700} />
              <Text style={styles.stateText}>Loading notifications...</Text>
            </View>
          ) : error ? (
            <View style={styles.stateWrap}>
              <Ionicons name="alert-circle-outline" size={22} color={T.red700} />
              <Text style={styles.errorText}>{error}</Text>
            </View>
          ) : notifications.length === 0 ? (
            <View style={styles.stateWrap}>
              <Ionicons name="notifications-off-outline" size={24} color={T.slate400} />
              <Text style={styles.emptyTitle}>No notifications yet</Text>
              <Text style={styles.emptyText}>New appointment, queue, payment, and account updates will appear here.</Text>
            </View>
          ) : (
            <View style={styles.listWrap}>
              {notifications.map((item) => (
                <NotificationCard key={item.id} item={item} />
              ))}
            </View>
          )}
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
    paddingBottom: 22,
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
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    gap: 14,
    zIndex: 1,
  },
  headerTitleWrap: {
    flex: 1,
    paddingRight: 8,
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
    backgroundColor: 'rgba(255,255,255,0.7)',
  },
  eyebrowText: {
    fontSize: 9,
    fontWeight: '700',
    letterSpacing: 0.9,
    textTransform: 'uppercase',
    color: 'rgba(255,255,255,0.8)',
  },
  headerTitle: {
    fontSize: 30,
    fontWeight: '800',
    fontFamily: 'serif',
    color: T.white,
    letterSpacing: 0.2,
    lineHeight: 34,
  },
  headerSubtitle: {
    fontSize: 12,
    lineHeight: 18,
    color: 'rgba(255,255,255,0.78)',
    marginTop: 6,
  },
  backBtn: {
    width: 40,
    height: 40,
    borderRadius: 20,
    backgroundColor: 'rgba(255,255,255,0.15)',
    borderWidth: 1,
    borderColor: 'rgba(255,255,255,0.25)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  contentSurface: {
    flex: 1,
    backgroundColor: T.slate100,
    borderTopLeftRadius: 24,
    borderTopRightRadius: 24,
    paddingTop: 20,
    paddingHorizontal: 14,
    paddingBottom: 24,
  },
  sectionHeader: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 10,
    marginBottom: 16,
  },
  sectionTitle: {
    fontSize: 18,
    fontWeight: '700',
    color: T.slate800,
  },
  sectionSubtitle: {
    fontSize: 12,
    color: T.slate500,
    marginTop: 4,
  },
  sectionBadge: {
    minWidth: 34,
    height: 34,
    borderRadius: 17,
    paddingHorizontal: 10,
    backgroundColor: 'rgba(6,182,212,0.12)',
    alignItems: 'center',
    justifyContent: 'center',
  },
  sectionBadgeText: {
    fontSize: 12,
    fontWeight: '800',
    color: T.cyan700,
  },
  listWrap: {
    gap: 10,
  },
  notificationCard: {
    backgroundColor: T.white,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: T.slate200,
    padding: 14,
    flexDirection: 'row',
    alignItems: 'flex-start',
    gap: 12,
    shadowColor: '#0f172a',
    shadowOffset: { width: 0, height: 2 },
    shadowOpacity: 0.05,
    shadowRadius: 8,
    elevation: 2,
  },
  notificationIconWrap: {
    width: 40,
    height: 40,
    borderRadius: 12,
    alignItems: 'center',
    justifyContent: 'center',
    flexShrink: 0,
  },
  notificationContent: {
    flex: 1,
  },
  notificationTopRow: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    gap: 8,
    marginBottom: 4,
  },
  notificationTitle: {
    flex: 1,
    fontSize: 13,
    fontWeight: '700',
    color: T.slate800,
  },
  unreadDot: {
    width: 8,
    height: 8,
    borderRadius: 4,
    backgroundColor: T.cyan500,
    flexShrink: 0,
    marginTop: 3,
  },
  notificationBody: {
    fontSize: 12,
    lineHeight: 18,
    color: T.slate700,
  },
  notificationMeta: {
    fontSize: 11,
    color: T.slate500,
    marginTop: 8,
  },
  stateWrap: {
    backgroundColor: T.white,
    borderRadius: 18,
    borderWidth: 1,
    borderColor: T.slate200,
    paddingHorizontal: 22,
    paddingVertical: 30,
    alignItems: 'center',
    justifyContent: 'center',
  },
  stateText: {
    fontSize: 12,
    color: T.slate500,
    marginTop: 10,
  },
  errorText: {
    fontSize: 12,
    color: T.red700,
    lineHeight: 18,
    textAlign: 'center',
    marginTop: 10,
  },
  emptyTitle: {
    fontSize: 14,
    fontWeight: '700',
    color: T.slate700,
    marginTop: 10,
    marginBottom: 4,
  },
  emptyText: {
    fontSize: 12,
    lineHeight: 18,
    color: T.slate500,
    textAlign: 'center',
  },
});
