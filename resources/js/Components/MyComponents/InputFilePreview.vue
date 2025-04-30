<template>
  <div class="inline">
    <figure @click="triggerImageInput"
      class="flex items-center justify-center rounded-lg border border-grayD9 w-48 h-36 cursor-pointer relative">
      <i v-if="image && canDelete" @click.stop="clearImage"
        class="fa-solid fa-xmark absolute p-1 top-1 right-1 z-10 text-sm cursor-default hover:text-red-500"></i>
        <svg v-if="!image" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="text-gray-400 size-7">
          <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 0 0 1.5-1.5V6a1.5 1.5 0 0 0-1.5-1.5H3.75A1.5 1.5 0 0 0 2.25 6v12a1.5 1.5 0 0 0 1.5 1.5Zm10.5-11.25h.008v.008h-.008V8.25Zm.375 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Z" />
        </svg>
      <img v-if="image" :src="image" :alt="alt" class="w-full h-full object-contain bg-no-repeat rounded-md opacity-50" />
      <input ref="fileInput" type="file" @change="handleImageUpload" class="hidden" />
    </figure>
  </div>
</template>

<script>
export default {
  data() {
    return {
      image: null,
      formData: {
        file: null,
      },
    };
  },
  props: {
    alt: {
      type: String,
      default: "Vista previa no disponible",
    },
    canDelete: {
      type: Boolean,
      default: true,
    },
    imageUrl: {
      type: String,
      default: null,
    },
  },
  watch: {
    imageUrl: {
      immediate: true,
      handler(newImageUrl) {
        this.image = newImageUrl;
      },
    },
  },
  emits: ['imagen', 'cleared'],
  methods: {
    triggerImageInput() {
      this.$refs.fileInput.click();
    },
    handleImageUpload(event) {
      const file = event.target.files[0];
      this.formData.file = file;

      if (file) {
        const imageURL = URL.createObjectURL(file);
        this.image = imageURL;
        // Emitir evento al componente padre con la imagen
        this.$emit("imagen", file);
      }

    },
    clearImage() {
      this.image = null;
      this.formData.file = null;
      this.$emit("cleared");
    },
  },
};
</script>

