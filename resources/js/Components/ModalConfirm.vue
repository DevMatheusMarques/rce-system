<template>
    <transition name="modal">
        <div v-if="visible" id="modal-confirm" class="modal-wrapper" @keydown.esc="$emit('close')" tabindex="1">
            <div class="modal-overlay" @click.self="$emit('close')"></div>
            <div class="modal__component">
                <div class="modal__header">
                    <h2 class="header__title">Excluir {{ item }}</h2>
                    <span class="header__close" @click="$emit('close')">&times;</span>
                </div>
                <div class="modal__body">
                    <p>Uma vez confirmada, essa ação não poderá ser defeita. Deseja continuar com a exclusão do
                        {{ item }}?</p>
                    <p class="body__information">{{ additionalInformation }}</p>
                    <slot/>
                </div>
                <div class="modal__footer">
                    <ButtonSecondary @click="$emit('close')" name="Cancelar"/>
                    <ButtonDanger @click="$emit('delete')" :name="'Excluir ' + item"/>
                </div>
            </div>
        </div>
    </transition>
</template>

<script>
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import ButtonSecondary from "@/Components/ButtonSecondary.vue";
import ButtonDanger from "@/Components/ButtonDanger.vue";

export default {
    components: {ButtonDanger, ButtonSecondary, ButtonPrimary},
    props: {
        visible: {
            type: Boolean,
            default: false,
            required: true
        },
        item: {
            type: String,
            required: true
        },
        additionalInformation: {
            type: String,
            default: ''
        }
    },
    emits: ['close', 'delete'],
    updated() {
        if (this.visible) {
            document.querySelector('#modal-confirm').focus();
        }
    }
}
</script>

<style scoped>
@import "/resources/css/components/modal-confirm.css";
</style>
