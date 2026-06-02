import React from 'react';
import { StyleSheet, Text, View } from 'react-native';

export function DisclaimerBanner({ text }: { text: string }) {
  return (
    <View style={styles.banner}>
      <Text style={styles.icon}>⚠️</Text>
      <Text style={styles.text}>{text}</Text>
    </View>
  );
}

const styles = StyleSheet.create({
  banner: {
    flexDirection: 'row',
    gap: 8,
    backgroundColor: '#FEF9C3',
    borderRadius: 12,
    padding: 12,
  },
  icon: { fontSize: 14 },
  text: { flex: 1, fontSize: 12, color: '#854D0E', lineHeight: 17 },
});
