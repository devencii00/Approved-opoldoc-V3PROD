import React, { ReactNode } from 'react';
import { View, Text, StyleSheet, ViewStyle } from 'react-native';

type CardProps = {
  title?: string;
  subtitle?: string;
  badge?: string;
  children?: ReactNode;
  style?: ViewStyle | ViewStyle[];
  headerRight?: ReactNode;
};

export function Card({ title, subtitle, badge, children, style, headerRight }: CardProps) {
  const hasHeader = title || subtitle || badge || headerRight;

  return (
    <View style={[styles.card, style]}>
      {hasHeader ? (
        <View style={styles.headerRow}>
          <View style={styles.headerTextBlock}>
            {badge ? <Text style={styles.badge}>{badge}</Text> : null}
            {title ? <Text style={styles.title}>{title}</Text> : null}
            {subtitle ? <Text style={styles.subtitle}>{subtitle}</Text> : null}
          </View>
          {headerRight ? <View style={styles.headerRight}>{headerRight}</View> : null}
        </View>
      ) : null}
      {children}
    </View>
  );
}

const styles = StyleSheet.create({
  card: {
    borderRadius: 18,
    padding: 16,
    backgroundColor: '#ffffff',
    borderWidth: 1,
    borderColor: '#e2e8f0',
    shadowColor: '#0f172a',
    shadowOpacity: 0.04,
    shadowOffset: { width: 0, height: 2 },
    shadowRadius: 10,
    elevation: 2,
  },
  headerRow: {
    flexDirection: 'row',
    alignItems: 'flex-start',
    justifyContent: 'space-between',
    marginBottom: 8,
  },
  headerTextBlock: {
    flexShrink: 1,
  },
  headerRight: {
    marginLeft: 12,
  },
  badge: {
    fontSize: 11,
    textTransform: 'uppercase',
    letterSpacing: 1.2,
    color: '#94a3b8',
    marginBottom: 2,
  },
  title: {
    fontSize: 14,
    fontWeight: '600',
    color: '#0f172a',
  },
  subtitle: {
    fontSize: 12,
    color: '#64748b',
    marginTop: 2,
  },
});

