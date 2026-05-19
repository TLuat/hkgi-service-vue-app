import { ref, computed } from 'vue'

export function normalizePlate(plate: string): string {
  return plate.toUpperCase().replace(/\s/g, '')
}

export const normalizeLicensePlate = normalizePlate

export function formatPlate(plate: string): string {
  const p = normalizePlate(plate)
  if (p.length >= 7 && !p.includes('-')) {
    return `${p.slice(0, 3)}-${p.slice(3)}`
  }
  return p
}

export function useLicensePlate(initial = '') {
  const value      = ref(initial)
  const normalized = computed(() => normalizePlate(value.value))

  function handleInput(event: Event) {
    value.value = normalizePlate((event.target as HTMLInputElement).value)
  }

  return { value, normalized, handleInput }
}