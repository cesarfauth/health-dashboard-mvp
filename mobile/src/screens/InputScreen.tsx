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
      next.sleep_hours = 'Enter sleep hours as a number.';
    } else if (sleepNum < 0 || sleepNum > 24) {
      next.sleep_hours = 'Sleep must be between 0 and 24 hours.';
    }

    const glucoseNum = Number(glucose);
    if (glucose.trim() === '' || !Number.isInteger(glucoseNum)) {
      next.glucose_level = 'Enter glucose as a whole number (mg/dL).';
    } else if (glucoseNum < 20 || glucoseNum > 600) {
      next.glucose_level = 'Glucose must be between 20 and 600 mg/dL.';
    }

    const hrvNum = Number(hrv);
    if (hrv.trim() === '' || !Number.isInteger(hrvNum)) {
      next.hrv = 'Enter HRV as a whole number (ms).';
    } else if (hrvNum < 1 || hrvNum > 300) {
      next.hrv = 'HRV must be between 1 and 300 ms.';
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
        setFormError(e instanceof Error ? e.message : 'Something went wrong.');
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
        <Text style={styles.title}>New reading</Text>
        <Text style={styles.subtitle}>
          Enter today&apos;s biomarkers. We&apos;ll analyze them and suggest habits.
        </Text>

        <FormField
          label="Sleep"
          unit="hours"
          value={sleep}
          onChangeText={setSleep}
          placeholder="e.g. 7.5"
          error={errors.sleep_hours}
        />
        <FormField
          label="Glucose (fasting)"
          unit="mg/dL"
          value={glucose}
          onChangeText={setGlucose}
          placeholder="e.g. 92"
          error={errors.glucose_level}
        />
        <FormField
          label="HRV"
          unit="ms"
          value={hrv}
          onChangeText={setHrv}
          placeholder="e.g. 60"
          error={errors.hrv}
        />

        {formError ? <Text style={styles.formError}>{formError}</Text> : null}

        <PrimaryButton
          label="Analyze with AI"
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
