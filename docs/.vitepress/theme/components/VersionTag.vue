<template>
  <span class="version-tag" :style="versionStyle">
    {{ version }}
  </span>
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  version: {
    type: String,
    required: true,
  },
})

function generateVersionColor(version) {
  const cleanVersion = version.replace('v', '')
  const parts = cleanVersion.split('.').map(Number)
  const [major = 0, minor = 0, patch = 0] = parts

  const baseHue = (major * 90) % 360
  const progression = minor * 100 + patch
  const maxProgression = 9999
  const hueShift = (progression / maxProgression) * 180
  const hue = (baseHue + hueShift) % 360
  const saturation = Math.max(20, Math.min(40 + (progression / maxProgression) * 60, 85))
  const lightness = Math.max(50, Math.min(70 - (progression / maxProgression) * 30, 80))

  return { hue, saturation, lightness }
}

const versionStyle = computed(() => {
  const color = generateVersionColor(props.version)
  const isDark = typeof document !== 'undefined' && document.documentElement.classList.contains('dark')

  if (isDark) {
    return {
      background: `hsl(${color.hue}, ${color.saturation + 15}%, ${color.lightness + 8}%)`,
      color: `hsl(${color.hue}, ${color.saturation + 15}%, 15%)`,
      border: `1px solid hsl(${color.hue}, ${color.saturation + 15}%, 75%)`,
    }
  }

  const lightBackground = Math.max(color.lightness + 15, 75)
  const lightText = Math.max(color.lightness - 45, 20)

  return {
    background: `hsl(${color.hue}, ${Math.max(color.saturation - 10, 20)}%, ${lightBackground}%)`,
    color: `hsl(${color.hue}, ${Math.max(color.saturation - 10, 20)}%, ${lightText}%)`,
    border: `1px solid hsl(${color.hue}, ${Math.max(color.saturation - 10, 20)}%, 90%)`,
  }
})
</script>

<style scoped>
.version-tag {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  padding: 0.25rem 0.5rem;
  font-size: 0.75rem;
  font-weight: 600;
  border-radius: 0.375rem;
  text-transform: uppercase;
  letter-spacing: 0.025em;
  line-height: 1;
  white-space: nowrap;
  transition: all 0.2s ease;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
  margin: 0 0.25rem;
  vertical-align: top;
  margin-top: -0.125rem;
}

.version-tag:hover {
  transform: translateY(-1px);
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.12);
}

:root:not(.dark) .version-tag {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
  border: 1px solid rgba(0, 0, 0, 0.1);
}

:root:not(.dark) .version-tag:hover {
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
  border: 1px solid rgba(0, 0, 0, 0.15);
}

:root.dark .version-tag {
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.2);
}

:root.dark .version-tag:hover {
  box-shadow: 0 2px 6px rgba(0, 0, 0, 0.3);
}
</style>
