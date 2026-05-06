import React from 'react';
import { View, Text, StyleSheet, ViewStyle } from 'react-native';

type StatusVariant = 'scheduled' | 'completed' | 'cancelled' | 'active' | 'info';

type StatusPillProps = {
  label: string;
  variant?: StatusVariant;
  style?: ViewStyle | ViewStyle[];
};

export function StatusPill({ label, variant = 'info', style }: StatusPillProps) {
  const colors = getColors(variant);

  return (
    <View style={[styles.container, { backgroundColor: colors.bg, borderColor: colors.border }, style]}>
      <View style={[styles.dot, { backgroundColor: colors.dot }]} />
      <Text style={[styles.label, { color: colors.text }]}>{label}</Text>
    </View>
  );
}

function getColors(variant: StatusVariant) {
  if (variant === 'scheduled') {
    return {
      bg: 'rgba(59,130,246,0.08)',
      border: 'rgba(59,130,246,0.25)',
      dot: '#2563eb',
      text: '#1d4ed8',
    };
  }

  if (variant === 'completed') {
    return {
      bg: 'rgba(16,185,129,0.08)',
      border: 'rgba(16,185,129,0.25)',
      dot: '#059669',
      text: '#047857',
    };
  }

  if (variant === 'cancelled') {
    return {
      bg: 'rgba(248,113,113,0.08)',
      border: 'rgba(248,113,113,0.25)',
      dot: '#ef4444',
      text: '#b91c1c',
    };
  }

  if (variant === 'active') {
    return {
      bg: 'rgba(8,145,178,0.08)',
      border: 'rgba(8,145,178,0.25)',
      dot: '#0891b2',
      text: '#0e7490',
    };
  }

  return {
    bg: 'rgba(148,163,184,0.08)',
    border: 'rgba(148,163,184,0.25)',
    dot: '#64748b',
    text: '#475569',
  };
}

const styles = StyleSheet.create({
  container: {
    flexDirection: 'row',
    alignItems: 'center',
    alignSelf: 'flex-start',
    paddingHorizontal: 10,
    paddingVertical: 4,
    borderRadius: 999,
    borderWidth: 1,
  },
  dot: {
    width: 6,
    height: 6,
    borderRadius: 3,
    marginRight: 6,
  },
  label: {
    fontSize: 11,
    fontWeight: '500',
  },
});

