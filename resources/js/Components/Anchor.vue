<script>
export default {
    name: "Anchor",
    props: {
        name: String,
        href: String,
        id: String,
    }
}
</script>

<template>
    <a class="anchor" :id="id" :href="href">{{name}}</a>
</template>

<style scoped>
    .anchor {
        color: #269aef;
        font-size: 14px;
    }
    .anchor:hover {
        color: #1354ad;
        text-decoration: underline;
    }
    a {
        cursor: pointer;
    }
</style>
