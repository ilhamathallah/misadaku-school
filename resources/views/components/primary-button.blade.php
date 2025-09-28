<button {{ $attributes->merge(['type' => 'submit', 'class' => 'w-full mt-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg shadow-md transition']) }}>
    {{ $slot }}
</button>
