<div class="w-full h-full min-h-full flex flex-col">
    <x-page-title label="Chat interna" class="mt-1" />

    <div class="w-full flex rounded-lg">
        <div
            class=" hidden md:grid ps-6 pt-6  pb-4 pe-2  rounded-s-lg bg-white  relative w-full h-full md:w-[360px] lg:w-[400px] xl:w-[500px]  shrink-0 overflow-y-auto  ">
            <livewire:wirechat.chats />
        </div>

        <main
            class="  grid  w-full  grow  h-full min-h-min rounded-e-lg bg-white p-4 ps-2 pe-6 pt-6 relative overflow-y-auto"
            style="contain:content">
            <livewire:wirechat.chat conversation="{{ $this->conversation->id }}" />
        </main>

    </div>
</div>
