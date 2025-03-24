<template>
    <Head name="Redefinir Senha"/>
    <div class="content">
        <div class="content__box">
            <h1 class="content__title">Redefina sua senha</h1>
            <form action="/" method="post" id="form-reset-password" class="content__form">
                <label class="form__label" for="password">Nova Senha<span class="field-required">*</span></label><br>
                <input class="form__input"
                       @keydown.enter="resetSubmit"
                       @keydown="removeStyleInputError"
                       type="password" id="password" name="password" placeholder="Informe a nova senha" required/><br>

                <label class="form__label" for="password_confirmation">Confirmar Senha<span class="field-required">*</span></label><br>
                <input class="form__input"
                       @keydown.enter="resetSubmit"
                       @keydown="removeStyleInputError"
                       type="password" id="password_confirmation"
                       name="password_confirmation" placeholder="Confirme a nova senha" required/>
            </form>
            <div class="content__options">
                <Anchor class="anchor__forgot" href="/auth" name="Cancelar"/>
                <ButtonPrimary @click="resetSubmit" meuId="submit" class="button__specify" name="Redefinir"/>
            </div>
        </div>
    </div>
</template>

<script>
import Anchor from "@/Components/Anchor.vue";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import {Head} from "@inertiajs/vue3";
export default {
    name: "ResetPassword",
    components: {ButtonPrimary, Anchor, Head},
    mounted() {
        this.toast = useSwalAlert()
    },
    methods: {
        async resetSubmit() {
            const formResetPassword = document.getElementById("form-reset-password");
            const formData = new FormData(formResetPassword);
            const email = new URLSearchParams(window.location.search).get('email');
            const token = window.location.pathname.split('/').pop();

            try {
                const response = await axios.post('/password/reset', {
                    password: formData.get('password'),
                    password_confirmation: formData.get('password_confirmation'),
                    email: email,
                    token: token
                });
                await this.toast.fire({
                    icon: 'success',
                    title: response.data.message,
                });
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
    },
    data() {
        return {
            errors: [],
            toast: null
        }
    }
}
</script>

<style scoped>
@import "/resources/css/components/reset-password.css";
</style>
