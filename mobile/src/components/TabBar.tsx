import React from 'react';
import { Pressable, StyleSheet, Text, View } from 'react-native';

import { colors } from '../theme/colors';

export type TabKey = 'dashboard' | 'input' | 'trends' | 'history';

const TABS: { key: TabKey; label: string; icon: string }[] = [
  { key: 'dashboard', label: 'Painel', icon: '📊' },
  { key: 'input', label: 'Nova', icon: '➕' },
  { key: 'trends', label: 'Tendências', icon: '📈' },
  { key: 'history', label: 'Histórico', icon: '🕑' },
];

interface Props {
  active: TabKey;
  onChange: (tab: TabKey) => void;
}

export function TabBar({ active, onChange }: Props) {
  return (
    <View style={styles.bar}>
      {TABS.map((tab) => {
        const isActive = tab.key === active;
        return (
          <Pressable
            key={tab.key}
            style={styles.tab}
            onPress={() => onChange(tab.key)}
          >
            <Text style={[styles.icon, isActive ? styles.activeIcon : null]}>
              {tab.icon}
            </Text>
            <Text style={[styles.label, isActive ? styles.activeLabel : null]}>
              {tab.label}
            </Text>
          </Pressable>
        );
      })}
    </View>
  );
}

const styles = StyleSheet.create({
  bar: {
    flexDirection: 'row',
    backgroundColor: colors.surface,
    borderTopWidth: 1,
    borderTopColor: colors.border,
    paddingBottom: 6,
    paddingTop: 8,
  },
  tab: { flex: 1, alignItems: 'center', gap: 2 },
  icon: { fontSize: 20, opacity: 0.5 },
  activeIcon: { opacity: 1 },
  label: { fontSize: 11, color: colors.muted, fontWeight: '600' },
  activeLabel: { color: colors.primary, fontWeight: '800' },
});
