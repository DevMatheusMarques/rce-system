<script>
import axios from 'axios';
import Anchor from "@/Components/Anchor.vue";
import ButtonPrimary from "@/Components/ButtonPrimary.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import {Head} from "@inertiajs/vue3";

export default {
    name: "ForgotPassword",
    components: {Head, ButtonPrimary, Anchor},
    mounted() {
        this.toast = useSwalAlert()
    },
    methods: {
        async passwordForgot() {
            const formLogin = document.getElementById("form-forgot");
            const formData = new FormData(formLogin);

            try {
                const response = await axios.post('/password/forgot', {
                    email: formData.get('email'),
                });
                await useRemoveFormatInputError();
                await this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
            } catch (errors) {
                const response = errors.response;
                if (response.status === 422) {
                    const inputsIds = response.data.errors;
                    await useFormatInputError(inputsIds)
                } else {
                    await useRemoveFormatInputError();
                }

                this.errors = response.data.message;
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
        },
        showLogin() {
            this.$emit('show-login');
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

<template>
    <div class="content">
        <Head title="Recuperar Senha"/>
        <div class="content__logo">
            <img class="content__logo--img" src="/assets/img/logo.jpg" alt="logo da empresa rce">
        </div>
        <div class="content__title">
            <h1 class="content__title-title">Redefina sua senha</h1>
            <p class="content__title-subtitle">
                Para redefinir sua senha, digite abaixo o e-mail que você usa para acessar o RCE
            </p>
        </div>
        <div class="content__form">
            <form id="form-forgot">
                <label class="form__label" for="email">E-mail cadastrado<span class="field-required">*</span></label>
                <input class="form__input"
                       @keydown.prevent.enter="passwordForgot"
                       @keydown="removeStyleInputError"
                       type="email" id="email" name="email"
                       placeholder="Informe seu e-mail" required autofocus />
            </form>
        </div>

        <ButtonPrimary @click="passwordForgot" meuId="submit" class="button__specify"
                       name="Enviar link de recuperação"/>

        <Anchor @click.prevent.native="showLogin" class="anchor__forgot" name="Voltar ao Login"/>
    </div>
</template>

<style scoped>
@import "/resources/css/components/forgot-password.css";
</style>
