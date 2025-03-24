<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import ButtonSecondary from "@/Components/ButtonSecondary.vue";

export default {
    name: "ModalRight",
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
            document.querySelector('.modal-wrapper').focus();
        } else {
            const fullModal = document.querySelector('#full-screen-modal');
            if (fullModal) {
                fullModal.focus()
            }
        }
    }
}
</script>

<template>
    <transition name="modal-right">
        <div v-if="visible" class="modal-wrapper" @keydown.esc="$emit('close')" tabindex="1">
            <div class="modal-overlay" @click.self="$emit('close')"></div>
            <div class="modal__component">
                <div class="modal__header">
                    <img class="header__close--icon" @click="$emit('close')"
                         src="/assets/icons/arrow-left-blue-sidebar.svg" alt="icone de flexa para esquerda">
                    <h2 class="header__title">{{ title }}</h2>
                </div>
                <div class="modal__body">
                    <slot/>
                </div>
                <div class="modal__footer">
                    <ButtonSecondary @click="$emit('close')" name="Cancelar"/>
                    <ButtonPrimary @click="$emit('save')" name="Salvar Alterações"/>
                </div>
            </div>
        </div>
    </transition>
</template>


<style scoped>
@import "/resources/css/components/modal-right.css";
</style>
