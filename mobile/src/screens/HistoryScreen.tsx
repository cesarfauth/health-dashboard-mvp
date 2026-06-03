import React, { useState } from 'react';
import {
  ActivityIndicator,
  FlatList,
  Modal,
  Pressable,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import { DisclaimerBanner } from '../components/DisclaimerBanner';
import { useHealthData } from '../context/HealthDataContext';
import { colors } from '../theme/colors';
import type { AiRecommendation, HealthRecord } from '../types/health';

const CATEGORY_ICONS: Record<string, string> = {
  sleep: '🌙',
  nutrition: '🥗',
  activity: '🚶',
  stress: '🧘',
  general: '✅',
};

// ---------------------------------------------------------------------------
// Modal de detalhe de recomendação
// ---------------------------------------------------------------------------
function RecommendationModal({
  record,
  onClose,
}: {
  record: HealthRecord;
  onClose: () => void;
}) {
  const rec = record.recommendation as AiRecommendation;
  const { sleep_hours, glucose_level, hrv } = record.biomarkers;

  return (
    <Modal
      visible
      animationType="slide"
      presentationStyle="pageSheet"
      onRequestClose={onClose}
    >
      <View style={modal.container}>
        {/* Cabeçalho */}
        <View style={modal.header}>
          <Text style={modal.title}>Detalhes da leitura</Text>
          <Pressable onPress={onClose} style={modal.closeBtn} hitSlop={12}>
            <Text style={modal.closeText}>✕ Fechar</Text>
          </Pressable>
        </View>

        <ScrollView
          contentContainerStyle={modal.content}
          showsVerticalScrollIndicator={false}
        >
          {/* Data */}
          <Text style={modal.date}>
            {new Date(record.recorded_at).toLocaleString('pt-BR')}
          </Text>

          {/* Biomarcadores */}
          <View style={modal.bioRow}>
            {[sleep_hours, glucose_level, hrv].map((b, i) => (
              <View
                key={i}
                style={[modal.bioCard, { borderTopColor: b.color }]}
              >
                <Text style={modal.bioValue}>
                  {b.value}
                  <Text style={modal.bioUnit}> {b.unit}</Text>
                </Text>
                <Text style={modal.bioLabel}>{b.label}</Text>
                <Text style={[modal.bioStatus, { color: b.color }]}>
                  {b.status_label}
                </Text>
              </View>
            ))}
          </View>

          {/* Resumo da IA */}
          <View style={modal.section}>
            <Text style={modal.sectionTitle}>Resumo da IA</Text>
            <Text style={modal.summary}>{rec.summary}</Text>
          </View>

          {/* Recomendações completas */}
          <View style={modal.section}>
            <Text style={modal.sectionTitle}>Recomendações</Text>
            {rec.recommendations.map((r, i) => (
              <View key={i} style={modal.recItem}>
                <Text style={modal.recIcon}>
                  {CATEGORY_ICONS[r.category] ?? '•'}
                </Text>
                <View style={modal.recBody}>
                  <Text style={modal.recTitle}>{r.title}</Text>
                  <Text style={modal.recDetail}>{r.detail}</Text>
                </View>
              </View>
            ))}
          </View>

          <DisclaimerBanner text={rec.disclaimer} />
        </ScrollView>
      </View>
    </Modal>
  );
}

// ---------------------------------------------------------------------------
// Card resumido da lista
// ---------------------------------------------------------------------------
function HistoryRow({
  record,
  onPress,
}: {
  record: HealthRecord;
  onPress: () => void;
}) {
  const { sleep_hours, glucose_level, hrv } = record.biomarkers;
  const hasRec = Boolean(record.recommendation);

  return (
    <Pressable
      onPress={hasRec ? onPress : undefined}
      style={({ pressed }) => [
        styles.row,
        pressed && hasRec ? styles.rowPressed : null,
      ]}
    >
      <View style={styles.rowTop}>
        <Text style={styles.rowDate}>
          {new Date(record.recorded_at).toLocaleString('pt-BR')}
        </Text>
        {hasRec && <Text style={styles.tapHint}>Ver recomendação →</Text>}
      </View>

      <View style={styles.metrics}>
        {[sleep_hours, glucose_level, hrv].map((b, i) => (
          <View key={i} style={styles.metric}>
            <View style={[styles.dot, { backgroundColor: b.color }]} />
            <Text style={styles.metricText}>
              {b.label.split(' ')[0]}: {b.value}
              {b.unit}
            </Text>
          </View>
        ))}
      </View>

      {record.recommendation ? (
        <Text style={styles.summary} numberOfLines={2}>
          {record.recommendation.summary}
        </Text>
      ) : null}
    </Pressable>
  );
}

// ---------------------------------------------------------------------------
// Tela principal
// ---------------------------------------------------------------------------
export function HistoryScreen() {
  const { records, loading, error, refresh } = useHealthData();
  const [selected, setSelected] = useState<HealthRecord | null>(null);

  if (loading && records.length === 0) {
    return (
      <View style={styles.centered}>
        <ActivityIndicator size="large" color={colors.primary} />
      </View>
    );
  }

  return (
    <>
      <FlatList
        data={records}
        keyExtractor={(item) => String(item.id)}
        renderItem={({ item }) => (
          <HistoryRow record={item} onPress={() => setSelected(item)} />
        )}
        contentContainerStyle={styles.content}
        onRefresh={refresh}
        refreshing={loading}
        ListHeaderComponent={<Text style={styles.title}>Histórico</Text>}
        ListEmptyComponent={
          <Text style={styles.empty}>
            {error ?? 'Nenhuma leitura registrada ainda.'}
          </Text>
        }
      />

      {selected?.recommendation ? (
        <RecommendationModal
          record={selected}
          onClose={() => setSelected(null)}
        />
      ) : null}
    </>
  );
}

// ---------------------------------------------------------------------------
// Estilos
// ---------------------------------------------------------------------------
const styles = StyleSheet.create({
  content: { padding: 16, gap: 12 },
  centered: { flex: 1, alignItems: 'center', justifyContent: 'center' },
  title: {
    fontSize: 24,
    fontWeight: '800',
    color: colors.text,
    marginBottom: 4,
  },
  row: {
    backgroundColor: colors.surface,
    borderRadius: 14,
    padding: 14,
    gap: 8,
  },
  rowPressed: { opacity: 0.75 },
  rowTop: {
    flexDirection: 'row',
    justifyContent: 'space-between',
    alignItems: 'center',
  },
  rowDate: { fontSize: 13, fontWeight: '700', color: colors.text },
  tapHint: { fontSize: 11, color: colors.primary, fontWeight: '700' },
  metrics: { flexDirection: 'row', flexWrap: 'wrap', gap: 12 },
  metric: { flexDirection: 'row', alignItems: 'center', gap: 5 },
  dot: { width: 9, height: 9, borderRadius: 999 },
  metricText: { fontSize: 12, color: colors.muted, fontWeight: '600' },
  summary: { fontSize: 12, color: colors.muted, fontStyle: 'italic' },
  empty: { textAlign: 'center', color: colors.muted, marginTop: 40 },
});

const modal = StyleSheet.create({
  container: { flex: 1, backgroundColor: colors.background },
  header: {
    flexDirection: 'row',
    alignItems: 'center',
    justifyContent: 'space-between',
    paddingHorizontal: 16,
    paddingVertical: 14,
    backgroundColor: colors.surface,
    borderBottomWidth: 1,
    borderBottomColor: colors.border,
  },
  title: { fontSize: 17, fontWeight: '800', color: colors.text },
  closeBtn: { padding: 4 },
  closeText: { fontSize: 14, color: colors.primary, fontWeight: '700' },
  content: { padding: 16, gap: 16 },
  date: { fontSize: 13, color: colors.muted, fontWeight: '600' },
  bioRow: { flexDirection: 'row', gap: 10 },
  bioCard: {
    flex: 1,
    backgroundColor: colors.surface,
    borderRadius: 12,
    borderTopWidth: 4,
    padding: 12,
    gap: 3,
    alignItems: 'center',
  },
  bioValue: { fontSize: 20, fontWeight: '800', color: colors.text },
  bioUnit: { fontSize: 11, fontWeight: '600', color: colors.muted },
  bioLabel: { fontSize: 11, color: colors.muted, textAlign: 'center' },
  bioStatus: { fontSize: 11, fontWeight: '700', textAlign: 'center' },
  section: {
    backgroundColor: colors.surface,
    borderRadius: 14,
    padding: 14,
    gap: 10,
  },
  sectionTitle: { fontSize: 15, fontWeight: '800', color: colors.text },
  summary: { fontSize: 14, color: colors.text, lineHeight: 21 },
  recItem: { flexDirection: 'row', gap: 10, alignItems: 'flex-start' },
  recIcon: { fontSize: 18, width: 24, textAlign: 'center' },
  recBody: { flex: 1, gap: 3 },
  recTitle: { fontSize: 14, fontWeight: '700', color: colors.text },
  recDetail: { fontSize: 13, color: colors.muted, lineHeight: 19 },
});
