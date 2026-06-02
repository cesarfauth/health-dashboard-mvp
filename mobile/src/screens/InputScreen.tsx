import React, { useState } from 'react';
import {
  KeyboardAvoidingView,
  Platform,
  ScrollView,
  StyleSheet,
  Text,
  View,
} from 'react-native';

import { FormField } from '../components/FormField';
import { PrimaryButton } from '../components/PrimaryButton';
import { useHealthData } from '../context/HealthDataContext';
import { ApiValidationError, createHealthRecord } from '../services/healthApi';
import { colors } from '../theme/colors';

type Errors = Record<string, string>;

interface Props {
  onCreated: () => void;
}

export function InputScreen({ onCreated }: Props) {
  const { refresh } = useHealthData();

  const [sleep, setSleep] = useState('');
  const [glucose, setGlucose] = useState('');
  const [hrv, setHrv] = useState('');

  const [errors, setErrors] = useState<Errors>({});
  const [formError, setFormError] = useState<string | null>(null);
  const [submitting, setSubmitting] = useState(false);

  function validate(): Errors {
    const next: Errors = {};

    const sleepNum = Number(sleep);
    if (sleep.trim() === '' || Number.isNaN(sleepNum)) {
      next.sleep_hours = 'Informe as horas de sono como número.';
    } else if (sleepNum < 0 || sleepNum > 24) {
      next.sleep_hours = 'O sono deve estar entre 0 e 24 horas.';
    }

    const glucoseNum = Number(glucose);
    if (glucose.trim() === '' || !Number.isInteger(glucoseNum)) {
      next.glucose_level = 'Informe a glicose como número inteiro (mg/dL).';
    } else if (glucoseNum < 20 || glucoseNum > 600) {
      next.glucose_level = 'A glicose deve estar entre 20 e 600 mg/dL.';
    }

    const hrvNum = Number(hrv);
    if (hrv.trim() === '' || !Number.isInteger(hrvNum)) {
      next.hrv = 'Informe a HRV como número inteiro (ms).';
    } else if (hrvNum < 1 || hrvNum > 300) {
      next.hrv = 'A HRV deve estar entre 1 e 300 ms.';
    }

    return next;
  }

  async function handleSubmit() {
    setFormError(null);
    const clientErrors = validate();
    setErrors(clientErrors);
    if (Object.keys(clientErrors).length > 0) {
      return;
    }

    setSubmitting(true);
    try {
      await createHealthRecord({
        sleep_hours: Number(sleep),
        glucose_level: Number(glucose),
        hrv: Number(hrv),
      });
      await refresh();
      setSleep('');
      setGlucose('');
      setHrv('');
      setErrors({});
      onCreated();
    } catch (e) {
      if (e instanceof ApiValidationError) {
        const mapped: Errors = {};
        Object.entries(e.fieldErrors).forEach(([field, msgs]) => {
          mapped[field] = msgs[0];
        });
        setErrors(mapped);
      } else {
        setFormError(e instanceof Error ? e.message : 'Algo deu errado.');
      }
    } finally {
      setSubmitting(false);
    }
  }

  return (
    <KeyboardAvoidingView
      style={styles.flex}
      behavior={Platform.OS === 'ios' ? 'padding' : undefined}
    >
      <ScrollView contentContainerStyle={styles.content}>
        <Text style={styles.title}>Nova leitura</Text>
        <Text style={styles.subtitle}>
          Insira os biomarcadores de hoje. Vamos analisá-los e sugerir hábitos.
        </Text>

        <FormField
          label="Sono"
          unit="horas"
          value={sleep}
          onChangeText={setSleep}
          placeholder="ex: 7.5"
          error={errors.sleep_hours}
        />
        <FormField
          label="Glicose (jejum)"
          unit="mg/dL"
          value={glucose}
          onChangeText={setGlucose}
          placeholder="ex: 92"
          error={errors.glucose_level}
        />
        <FormField
          label="HRV"
          unit="ms"
          value={hrv}
          onChangeText={setHrv}
          placeholder="ex: 60"
          error={errors.hrv}
        />

        {formError ? <Text style={styles.formError}>{formError}</Text> : null}

        <PrimaryButton
          label="Analisar com IA"
          onPress={handleSubmit}
          loading={submitting}
        />
      </ScrollView>
    </KeyboardAvoidingView>
  );
}

const styles = StyleSheet.create({
  flex: { flex: 1 },
  content: { padding: 20, gap: 16 },
  title: { fontSize: 26, fontWeight: '800', color: colors.text },
  subtitle: { fontSize: 14, color: colors.muted, marginTop: -8 },
  formError: { color: colors.danger, fontWeight: '600', fontSize: 13 },
});
