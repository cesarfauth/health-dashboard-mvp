import React from 'react';
import { StyleSheet, Text, TextInput, View } from 'react-native';

import { colors } from '../theme/colors';

interface Props {
  label: string;
  value: string;
  onChangeText: (v: string) => void;
  placeholder?: string;
  unit?: string;
  error?: string;
}

export function FormField({
  label,
  value,
  onChangeText,
  placeholder,
  unit,
  error,
}: Props) {
  return (
    <View style={styles.wrapper}>
      <Text style={styles.label}>
        {label}
        {unit ? <Text style={styles.unit}> ({unit})</Text> : null}
      </Text>
      <TextInput
        style={[styles.input, error ? styles.inputError : null]}
        value={value}
        onChangeText={onChangeText}
        placeholder={placeholder}
        placeholderTextColor={colors.muted}
        keyboardType="numeric"
        inputMode="decimal"
      />
      {error ? <Text style={styles.error}>{error}</Text> : null}
    </View>
  );
}

const styles = StyleSheet.create({
  wrapper: { gap: 6 },
  label: { fontSize: 14, fontWeight: '700', color: colors.text },
  unit: { fontSize: 13, fontWeight: '500', color: colors.muted },
  input: {
    backgroundColor: colors.surface,
    borderWidth: 1,
    borderColor: colors.border,
    borderRadius: 12,
    paddingHorizontal: 14,
    paddingVertical: 12,
    fontSize: 16,
    color: colors.text,
  },
  inputError: { borderColor: colors.danger },
  error: { fontSize: 12, color: colors.danger, fontWeight: '600' },
});
