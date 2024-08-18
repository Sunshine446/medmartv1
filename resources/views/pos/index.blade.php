<x-layout>
    <h1>Point of Sale</h1>

    @if (session('success'))
        <p class="text-green-500">{{ session('success') }}</p>
    @endif

    @if (session('error'))
        <p class="text-red-500">{{ session('error') }}</p>
    @endif

    <div class="card mb-3">
        <h2>Select Product</h2>

        <div class="mb-4">
            <input type="text" id="search-input" placeholder="Search products..." class="input w-full">
        </div>

        <div class="product-container overflow-y-auto max-h-96 flex flex-wrap">
            @foreach ($products as $product)
                <div class="product-card card sm:w-60 w-full border p-4 m-2 cursor-pointer" data-id="{{ $product->id }}"
                    data-name="{{ $product->product_name }}" data-price="{{ $product->price }}">
                    <div class="divide-y grid grid-cols-1 gap-2">
                        <h3 class="font-bold">{{ $product->product_name }}</h3>
                        @if ($product->generic_name)
                            <p>Generic Name: {{ $product->generic_name }}</p>
                        @else
                            <p class="text-gray-500 italic">No generic name</p>
                        @endif
                    </div>
                    <p>Available Inventory: {{ $product->total_inventory }}</p>
                    <p>₱{{ $product->price }}</p>
                </div>
            @endforeach
        </div>

        <form action="{{ route('pos.addItem') }}" method="POST" id="add-to-sale-form" class="hidden">
            @csrf
            <input type="hidden" name="product_id" id="selected-product-id">
            <div>
                <label for="quantity">Quantity:</label>
                <input type="number" name="quantity" id="quantity" class="input">
            </div>

            <div class="flex justify-center mt-4">
                <button type="submit" class="btn text-lg">Add to Sale</button>
            </div>
        </form>
    </div>

    <div class="card">
        <h2>Sale Items</h2>
        @if (!empty($saleDetails))
            @php
                $totalPrice = 0;
            @endphp
            <div class="overflow-x-auto sm:overflow-x-visible">
                <table class="w-full text-left rtl:text-right">
                    <thead class="uppercase">
                        <tr class="sm:hidden">
                            <th scope="col" class="px-2 py-3">Product Details</th>
                        </tr>
                        <tr class="hidden sm:table-row">
                            <th scope="col" class="px-2 sm:px-4 py-3">Product</th>
                            <th scope="col" class="px-2 sm:px-4 py-3">Qty</th>
                            <th scope="col" class="px-2 sm:px-4 py-3">Price</th>
                            <th scope="col" class="px-2 sm:px-4 py-3">Total</th>
                            <th scope="col" class="px-2 sm:px-4 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($saleDetails as $detail)
                            @php
                                $product = \App\Models\Product::find($detail['product_id']);
                                $totalPrice += $detail['quantity'] * $detail['price'];
                            @endphp
                            <tr
                                class="even:bg-white even:dark:bg-gray-200 odd:bg-gray-50 odd:dark:bg-white dark:border-gray-700 block sm:table-row mb-4 sm:mb-0">
                                <td class="px-2 sm:px-4 py-2 sm:py-4 flex flex-col sm:table-cell">
                                    <span class="font-bold sm:hidden">Product:</span>
                                    {{ $product->product_name }}
                                    <div class="sm:hidden mt-2">
                                        <span class="font-bold">Quantity:</span> {{ $detail['quantity'] }}<br>
                                        <span class="font-bold">Price:</span> ₱{{ $detail['price'] }}<br>
                                        <span class="font-bold">Total:</span>
                                        ₱{{ $detail['quantity'] * $detail['price'] }}
                                    </div>
                                </td>
                                <td class="px-2 sm:px-4 py-2 sm:py-4 hidden sm:table-cell">{{ $detail['quantity'] }}
                                </td>
                                <td class="px-2 sm:px-4 py-2 sm:py-4 hidden sm:table-cell">₱{{ $detail['price'] }}</td>
                                <td class="px-2 sm:px-4 py-2 sm:py-4 hidden sm:table-cell">
                                    ₱{{ $detail['quantity'] * $detail['price'] }}</td>
                                <td class="px-2 sm:px-4 py-2 sm:py-4 flex flex-col sm:flex-row gap-2">
                                    <form action="{{ route('pos.removeItem') }}" method="POST" class="inline">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $detail['product_id'] }}">
                                        <button type="submit"
                                            class="font-medium text-red-600 dark:text-red-500 hover:underline">Remove</button>
                                    </form>
                                    <form action="{{ route('pos.updateItem') }}" method="POST"
                                        class="inline flex-col sm:flex-row items-start sm:items-center gap-2">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $detail['product_id'] }}">
                                        <input type="number" name="quantity" value="{{ $detail['quantity'] }}"
                                            min="1" class="px-2 py-1 border rounded w-20">
                                        <button type="submit"
                                            class="font-medium text-blue-600 dark:text-blue-500 hover:underline">Update</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form action="{{ route('pos.applyDiscount') }}" method="POST">
                @csrf
                <div>
                    <label for="discount_percentage">Discount (%):</label>
                    <input type="number" name="discount_percentage" id="discount_percentage" class="input w-16"
                        value="{{ $discountPercentage }}" min="0" max="100">
                </div>
                <div class="flex justify-center mt-4">
                    <button type="submit" class="btn text-lg">Apply Discount</button>
                </div>
            </form>

            <div class="mt-4">
                <h3>Total Price: ₱{{ $totalPrice }}</h3>
                <h3>Discount: {{ $discountPercentage }}%</h3>
                <h3>Final Price: ₱{{ $totalPrice * (1 - $discountPercentage / 100) }}</h3>
            </div>

            <form action="{{ route('pos.checkout') }}" method="POST">
                @csrf
                <button type="submit" class="px-6 py-2 mt-4 bg-blue-600 text-white rounded">Checkout</button>
            </form>
        @endif
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const productCards = document.querySelectorAll('.product-card');
            const addToSaleForm = document.getElementById('add-to-sale-form');
            const selectedProductIdInput = document.getElementById('selected-product-id');
            const searchInput = document.getElementById('search-input');

            // Search functionality
            searchInput.addEventListener('input', function() {
                const query = this.value.toLowerCase();
                productCards.forEach(card => {
                    const productName = card.getAttribute('data-name').toLowerCase();
                    if (productName.includes(query)) {
                        card.classList.remove('hidden');
                    } else {
                        card.classList.add('hidden');
                    }
                });
            });

            productCards.forEach(card => {
                card.addEventListener('click', function() {
                    productCards.forEach(card => card.classList.remove('ring', 'ring-green-500',
                        'bg-green-50'));

                    this.classList.add('ring', 'ring-green-500', 'bg-green-50');

                    const productId = this.getAttribute('data-id');
                    selectedProductIdInput.value = productId;
                    addToSaleForm.classList.remove('hidden');
                });
            });
        });
    </script>
</x-layout>
