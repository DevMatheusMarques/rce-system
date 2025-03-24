<script>
import Anchor from "@/Components/Anchor.vue";
import FullScreenModal from "@/Components/FullScreenModal.vue";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import InputImage from "@/Components/InputImage.vue";

export default {
    name: "Profile",
    components: {InputImage, FullScreenModal, Anchor},
    mounted() {
        this.toast = useSwalAlert({}, 2000);
    },
    props: {
        visible: Boolean,
        authUser: Object,
        urlProfilePicture: String,
    },
    data() {
      return {
          authUserSidebar: this.authUser.sidebar
      }
    },
    emits: ['close', 'save', 'openEmail', 'openPassword', 'profileUpdated'],
    methods: {
        async profileUpdate() {
            const formProfile = document.getElementById("form-profile");
            const formData = new FormData(formProfile);
            const endpointRoute = '/' + this.authUser.level + '/user/update/' + this.authUser.id;

            try {
                const user = {
                    name: formData.get('name'),
                    sidebar: formData.get('sidebar')
                }
                if (user.name === this.authUser.name && user.sidebar === this.authUser.sidebar) {
                    this.toast.fire({
                        icon: 'warning',
                        title: 'Nada para atualizar'
                    });
                    return
                }
                const response = await axios.put(endpointRoute, user);
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                this.$emit('profileUpdated', response.data.data);
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
        },
        handleImageUpdated(authUser) {
            this.$emit('profileUpdated', authUser)
        }
    }
}
</script>

<template>
    <FullScreenModal
        :visible="visible"
        @close="$emit('close')"
        @save="profileUpdate"
        title="Meus dados"
    >
        <div class="user__info">
            <h2 class="info__title">
                Informações do Usuário
            </h2>
            <div class="form__info">
                <form action="" class="form__info--item form" method="post" enctype="multipart/form-data"
                      id="form-profile">
                    <div class="form__item">
                        <label class="form__label" for="name">Nome<span class="field-required">*</span></label>
                        <input class="form__input"
                               @keydown.prevent.enter="profileUpdate"
                               @keydown="removeStyleInputError"
                               type="text" id="name" name="name" required :value="authUser.name"/>
                    </div>
                    <div class="form__item">
                        <label class="form__label" for="email">
                            E-mail<span class="field-required">*</span>
                        </label>
                        <p class="form__input-disabled"> {{ authUser.email }}</p>

                        <div class="form__link">
                            <Anchor name="Alterar meu e-mail"
                                    @click.prevent="$emit('openEmail')"
                            />
                        </div>
                    </div>
                    <div class="form__item">
                        <label class="form__label" for="password">
                            Senha<span class="field-required">*</span>
                        </label>
                        <p class="form__input-disabled">********</p>

                        <Anchor name="Alterar minha senha"
                                @click.prevent="$emit('openPassword')"
                        />
                    </div>
                    <div class="form__item">
                        <p class="preferences__user--title">Preferências do Usuário</p>
                        <div class="preferences__user">
                            <p class="preferences__user--subtitle">Definições da barra de navegação</p>
                            <label for="sidebar" class="form__label">Abertura/Fechamento</label>
                            <select class="form__input" name="sidebar" id="sidebar"
                                    @change="removeStyleInputError"
                                    v-model="authUserSidebar"
                            >
                                <option value="hover">Hover - Abre e fecha ao passar o mouse sobre a barra de navegação.</option>
                                <option value="open-lock">Aberto - A barra de navegação fica por padrão aberta.</option>
                                <option value="close-lock">Fechado - A barra de navegação fica por padrão fechada.</option>
                            </select>
                        </div>
                    </div>
                </form>
                <div class="form__info--item">
                    <InputImage
                        :auth-user="authUser"
                        :picture-path="authUser.profile_picture_path"
                        router="/user/profile/picture"
                        @image-updated="handleImageUpdated"
                    />

                </div>
            </div>
        </div>
    </FullScreenModal>
</template>

<style scoped>
@import "/resources/css/components/user/profile.css";
</style>
