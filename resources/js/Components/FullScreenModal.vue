<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import ButtonSecondary from "@/Components/ButtonSecondary.vue";

export default {
    components: {ButtonSecondary, ButtonPrimary},
    props: {
        visible: {
            type: Boolean,
            default: false
        },
        title: String,
    },
    emits: ['close', 'save'],
    updated() {
        if (this.visible) {
            document.querySelector('#full-screen-modal').focus();
        }
    }
}
</script>

<template>
    <transition name="modal">
        <div class="modal__component" v-if="visible" @keydown.esc="$emit('close')" tabindex="0" id="full-screen-modal">
            <div class="modal__header">
                <h2 class="header__title">{{ title }}</h2>
                <span class="header__close" @click="$emit('close')">&times;</span>
            </div>
            <div class="modal__body">
                <slot/>
            </div>
            <div class="modal__footer">
                <ButtonSecondary @click="$emit('close')" name="Cancelar"/>
                <ButtonPrimary @click="$emit('save')" name="Salvar Alterações"/>
            </div>
        </div>
    </transition>
</template>


<style scoped>
@import "/resources/css/components/full-screen-modal.css";
</style>
