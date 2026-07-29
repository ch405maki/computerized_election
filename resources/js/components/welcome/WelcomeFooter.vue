<template>
    <div class="fixed bottom-0 left-0 z-50 w-full border-t bg-purple-900 px-8 py-4 shadow-sm">
        <footer class="flex items-center justify-between space-x-4">
            <span class="text-sm uppercase tracking-widest text-white"> AUSL Election System</span>

            <div class="flex items-center space-x-4 tracking-widest">
                <span class="text-sm text-white">Powered by:</span>
                <img src="/images/logo/itc-logo.png" alt="itc-Logo" class="h-10 w-auto" />
                <span class="border-black text-sm text-white"
                    >Information Technology Center<br />
                    <p class="text-xs">All Rights Reserved &copy; {{ new Date().getFullYear() }}</p>
                </span>
            </div>
        </footer>
    </div>
</template>
