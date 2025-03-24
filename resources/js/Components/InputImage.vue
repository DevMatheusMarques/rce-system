<script>
import Anchor from "@/Components/Anchor.vue";
import useRemoveFormatInputError from "@/Composables/useRemoveFormatInputError.js";
import axios from "axios";
import useFormatInputError from "@/Composables/useFormatInputError.js";
import useSwalAlert from "@/Composables/useSwalAlert.js";
import login from "@/Pages/Auth1/Login.vue";

export default {
    name: "InputImage",
    computed: {
        login() {
            return login
        }
    },
    components: {Anchor},
    emits: ['imageUpdated'],
    data() {
        return {
            imgTemporary: '',
            loading: false,
            myImage: null,
        }
    },
    props: {
        picturePath: String,
        router: String,
        authUser: {
            type: Object,
        },
        selfManagement: {
            type: Boolean,
            default: true
        },
        imageSelected: ''

    },
    mounted() {
        this.toast = useSwalAlert({}, 2000);
        this.myImage = this.imageSelected;
    },
    methods: {
        changeSelectedProfilePicture(event) {
            useRemoveFormatInputError();
            const file = event.target.files[0];
            if (file) {
                this.imgTemporary = URL.createObjectURL(file);
            }
        },
        async removeProfilePicture() {
            await this.pictureUpdate('remove');
            this.preloadProfileUrl = '';
        },
        async pictureUpdate(action = '') {
            this.loading = true;
            document.getElementById('remove').style.display = 'none';

            const formLogin = document.getElementById("form-picture-img");
            const formData = new FormData(formLogin);
            const endpointRoute = '/' + this.authUser.level + this.router;
            let picture = '';
            if (action === 'remove') {
                picture = null
            } else {
                picture = formData.get('picture');
            }
            try {
                const response = await axios.post(endpointRoute, {
                    picture: picture,
                }, {
                    headers: {
                        'Content-Type': 'multipart/form-data'
                    }
                });
                this.toast.fire({
                    icon: 'success',
                    title: response.data.message
                });
                this.$emit('imageUpdated', response.data.data);
            } catch (errors) {
                this.preloadProfileUrl = '';
                const response = errors.response;
                this.errors = response.data.message;

                if (response.status === 422) {
                    const inputsIds = errors.response.data.errors;
                    await useFormatInputError(inputsIds)
                }
                this.toast.fire({
                    icon: 'error',
                    title: this.errors
                });
            } finally {
                this.imgTemporary = '';
                this.loading = false;
            }

        },
        removePictureSelected() {
            this.myImage = '';
            this.imgTemporary = '';
            document.getElementById('picture').value = ''
        },
        handleSrcFile() {
            if (this.imgTemporary !== '') {
                return this.imgTemporary;
            } else if (this.picturePath !== null) {
                return this.picturePath;
            } else {
                return '/assets/img/avatar-example.jpg';
            }
        },
        handleSrcFileSelected() {
            if (this.imgTemporary !== '') {
                return this.imgTemporary;
            } else if (this.myImage !== '' && this.myImage !== null && this.myImage !== undefined) {
                return this.myImage;
            } else {
                return '/assets/img/no-image.jpg';
            }
        }
    },
}
</script>

<template>
    <div class="info__profile">
        <img
            v-if="loading"
            class="info__profile--img loading__background"
            src=""
            alt="">

        <Transition name="fade">
            <div v-if="!loading" id="remove">
                <img
                    v-if="!selfManagement"
                    class="info__img--img"
                    :src="handleSrcFileSelected()"
                    alt="">
                <img
                    v-if="selfManagement"
                    class="info__profile--img"
                    :src="handleSrcFile()"
                    alt="">
            </div>
        </Transition>
        <form action="" method="post" id="form-picture-img" enctype="multipart/form-data">
            <input
                @change="changeSelectedProfilePicture"
                class="info__profile--file" type="file"
                name="picture" id="picture">
        </form>
        <Anchor
            @click="removeProfilePicture"
            v-if="picturePath"
            class="picture__remove" name="Remover foto"/>
        <Anchor
            @click="removePictureSelected"
            v-if="!selfManagement"
            class="picture__remove" name="Remover foto"/>
    </div>
    <div class="pictures">
        <label for="picture"
               :class="['picture__upload', !selfManagement ? 'picture__upload--margin' : '']"
        >
            <img class="picture__upload--img" src="/assets/icons/upload.svg"
                 alt="imagem de uma nuvem com uma sete simulando upload"
            >
        </label>
        <button v-if="selfManagement" class="picture__save" @click="pictureUpdate">
            <img class="picture__remove--img" src="/assets/icons/check-solid.svg"
                 alt="imagem de um icone simulando checked">
        </button>
    </div>
</template>

<style scoped>
.fade-enter-active {
    transition: opacity 0.5s ease-in;
}

.fade-enter-from {
    opacity: 0;
}

@keyframes placeHolderShimmer {
    0% {
        background-position: -468px 0;
    }
    100% {
        background-position: 468px 0;
    }
}

.loading__background {
    animation-duration: 1.25s;
    animation-fill-mode: forwards;
    animation-iteration-count: infinite;
    animation-name: placeHolderShimmer;
    animation-timing-function: linear;
    background: linear-gradient(to right, var(--gray-light-hover) 10%, var(--gray-light) 18%, var(--gray-light-hover) 33%);
    background-size: 800px 104px;
    cursor: progress;
}


.info__img--img {
    width: 200px;
    height: 200px;
    border-radius: 15px;
    border: 1px solid var(--gray-dark);
    object-fit: contain;
}

.info__profile {
    display: flex;
    flex-direction: column;
    align-items: center;
}

.info__profile--img {
    width: 200px;
    height: 200px;
    border-radius: 50%;
    border: 3px dashed var(--gray-dark);
    object-fit: cover;
}

.info__profile--file {
    margin-top: 15px;
    display: none;
}

.pictures {
    display: flex;
    align-items: center;
    justify-content: space-around;
}

.picture__upload,
.picture__save {
    border-radius: 100%;
    padding: 7px;
}

.picture__save {
    background-color: var(--green-primary);
    transition: background-color .2s ease-in-out;
    margin-right: 20%;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.picture__save:active {
    box-shadow: none;
}

.picture__save:hover {
    background-color: var(--green-primary-hover);
}

.picture__upload--margin {
    margin-left: 0 !important;
}

.picture__upload {
    background-color: var(--gray-light);
    transition: background-color .2s ease-in-out;
    margin-left: 20%;
    border: 1px solid var(--gray-light-hover);
    cursor: pointer;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.15);
}

.picture__upload:active {
    box-shadow: none;
}

.picture__upload:hover {
    background-color: var(--gray-light-hover);
    border: 1px solid var(--gray-light-hover);
}

.picture__upload--img,
.picture__remove--img {
    width: 25px;
    height: 25px;
}

.picture__remove {
    color: red;
    margin-top: 10px;
}
</style>
