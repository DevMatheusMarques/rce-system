<script>
import ModalRight from "@/Components/ModalRight.vue";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";

export default {
    name: "UpdatePassword",
    components: {ModalRight},
    emits: ['close', 'save', 'userUpdated'],
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    props: {
        visible: Boolean,
        authUser: Object
    }, methods: {
        async passwordUpdate() {
            const formLogin = document.getElementById("form-password");
            const formData = new FormData(formLogin);
            const endpointRoute = '/' + this.authUser.level + '/password/reset/authenticated/';

            try {
                const response = await axios.post(endpointRoute, {
                    password: formData.get('password'),
                    password_confirmation: formData.get('password_confirmation'),
                    current_password: formData.get('current_password'),
                });
                await this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                setTimeout(() => {
                    this.$emit('close');
                }, 500)
                window.location.href = response.data.redirect;

            } catch (errors) {
                if (errors.response.status === 422) {
                    const inputsIds = errors.response.data.errors;
                    await useFormatInputError(inputsIds)
                }

                this.errors = errors.response.data.message;
                await this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            }
        },
        removeStyleInputError(event) {
            const input = event.target;
            if (input.classList.contains('input-error')) {
                input.classList.remove('input-error');
            }
        }
    }
}
</script>

<template>
    <ModalRight
        :visible="visible"
        @close="$emit('close')"
        @save="passwordUpdate"
        title="Alterar senha"
    >
        <form action="" class="form__password form" method="post" id="form-password">
            <div class="form__item">
                <label class="form__label" for="password">
                    Nova senha<span class="field-required">*</span></label>
                <input class="form__input"
                       @keydown.enter="passwordUpdate"
                       @keydown="removeStyleInputError"
                       type="password" id="password" name="password" required placeholder="Insira sua nova senha"/>
            </div>
            <div class="form__item">
                <label class="form__label" for="password_confirmation">
                    Confirmação de senha<span class="field-required">*</span>
                </label>
                <input class="form__input"
                       @keydown.enter="passwordUpdate"
                       @keydown="removeStyleInputError"
                       type="password" id="password_confirmation" name="password_confirmation" required
                       placeholder="Confirme sua nova senha"/>
            </div>
            <div class="form__item">
                <label class="form__label" for="current_password">
                    Senha atual<span class="field-required">*</span>
                </label>
                <input class="form__input"
                       @keydown.enter="passwordUpdate"
                       @keydown="removeStyleInputError"
                       type="password" name="current_password" required
                       placeholder="Informe sua nova senha"
                       :value="authUser.first_access ? 'password' : ''"
                />
            </div>
        </form>
    </ModalRight>
</template>

<style scoped>
.form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}
.form__item:last-of-type {
    margin-top: 25%;
    padding-top: .5rem;
    border-top: solid 1px var(--gray-dark);
}
</style>
