<template>
  <div v-if="properties">
    <div v-for="item in properties" :key="item.id" class="description-item">
      <template
        v-if="item.code == 'detail_text' || item.code == 'preview_text'"
      >
        <div class="description-label" style="margin-top:1rem;">
          {{ item.value }}
        </div>
      </template>

      <template v-else>
        <span class="description-label">{{ item.label }}: </span>
        <span v-if="typeof item.value == 'boolean'" class="description-value">{{
          item.value ? "Да" : "Нет"
        }}</span>
        <span v-else class="description-value">{{ item.value }}</span>
      </template>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    properties: {
      type: Array,
      required: false,
      default: null,
    },
  },
};
</script>
