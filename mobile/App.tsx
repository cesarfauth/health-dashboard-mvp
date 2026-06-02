import { StatusBar } from 'expo-status-bar';
import React, { useState } from 'react';
import { SafeAreaView, StyleSheet, Text, View } from 'react-native';

import { TabBar, type TabKey } from './src/components/TabBar';
import { HealthDataProvider } from './src/context/HealthDataContext';
import { DashboardScreen } from './src/screens/DashboardScreen';
import { HistoryScreen } from './src/screens/HistoryScreen';
import { InputScreen } from './src/screens/InputScreen';
import { TrendScreen } from './src/screens/TrendScreen';
import { colors } from './src/theme/colors';

export default function App() {
  const [tab, setTab] = useState<TabKey>('dashboard');

  return (
    <HealthDataProvider>
      <SafeAreaView style={styles.safe}>
        <StatusBar style="dark" />
        <View style={styles.header}>
          <Text style={styles.brand}>🩺 Health Dashboard</Text>
        </View>

        <View style={styles.body}>
          {tab === 'dashboard' && (
            <DashboardScreen onAddPress={() => setTab('input')} />
          )}
          {tab === 'input' && (
            <InputScreen onCreated={() => setTab('dashboard')} />
          )}
          {tab === 'trends' && <TrendScreen />}
          {tab === 'history' && <HistoryScreen />}
        </View>

        <TabBar active={tab} onChange={setTab} />
      </SafeAreaView>
    </HealthDataProvider>
  );
}

const styles = StyleSheet.create({
  safe: { flex: 1, backgroundColor: colors.background },
  header: {
    paddingHorizontal: 16,
    paddingVertical: 12,
    backgroundColor: colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  brand: { fontSize: 18, fontWeight: '800', color: colors.text },
  body: { flex: 1 },
});
