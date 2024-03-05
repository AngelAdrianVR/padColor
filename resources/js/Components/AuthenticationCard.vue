<template>
    <div class="min-h-screen flex flex-col justify-center items-center pt-6 sm:pt-0 bg-gray-100">

        <div class="w-11/12 h-[calc(100vh-5rem)] rounded-xl bg-[url('@/../../public/images/loginImage.jpg')] bg-cover bg-center bg-no-repeat">
            <div class="lg:w-1/4 w-full h-5/6 bg-white opacity-70 rounded-xl absolute top-14 left-40">
            </div>
            <div class="lg:w-1/4 w-full h-5/6 bg-transparent rounded-xl absolute top-14 left-40">
                <slot />
            </div>
        </div>
    </div>
</template>
