<template>
    <transition name="modal">
        <div v-if="visible" id="modal-preview" class="modal-wrapper" @keydown.esc="$emit('close')" tabindex="1">
            <div class="modal-overlay" @click.self="$emit('close')"></div>
            <div class="modal__component">
                <div class="modal__header">
                    <h2 class="header__title">Visualização do comprovante</h2>
                    <span class="header__close" @click="$emit('close')">&times;</span>
                </div>
                <div class="modal__body">
                    <img class="modal__body--img" v-if="isImage" :src="fileUrl" alt="pré-visualização da imagem">
                    <embed v-else-if="isPdf" :src="fileUrl" type="application/pdf" width="100%" height="600px">
                    <p v-else>Formato de arquivo não suportado para visualização.</p>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import useGetLastSegment from "@/Composables/useGetLastSegment.js";

export default {
    props: {
        visible: {
            type: Boolean,
            default: false,
            required: true
        },
        file: {
            required: true
        },
        url: {
            type: String,
            required: false
        }
    },
    data() {
        return {
            fileUrl: null
        };
    },
    computed: {
        currentExtension() {
            const extensionArray = useGetLastSegment(this.url).split('.');
            return extensionArray[extensionArray.length - 1];
        },
        isImage() {
            if (this.url) {
                const extensions = ['jpeg','png','jpg','gif','svg'];
                return extensions.includes(this.currentExtension);
            }
            return this.file.type.includes('image');
        },
        isPdf() {
            if (this.url) {
                return 'pdf' === this.currentExtension;
            }
            return this.file.type === 'application/pdf';
        }
    },
    mounted() {
        if (this.url) {
            this.fileUrl = this.url;
        }
        if (this.file) {
            this.fileUrl = URL.createObjectURL(this.file);
        }
    },
    beforeDestroy() {
        if (this.fileUrl) {
            URL.revokeObjectURL(this.fileUrl);
        }
    },
    updated() {
        document.querySelector('#modal-preview').focus();
    }
}
</script>

<style scoped>
@import "/resources/css/components/modal-preview.css";
</style>
