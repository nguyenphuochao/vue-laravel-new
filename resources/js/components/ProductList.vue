<template>
    <div class="api-calling container mt-5">

        <template v-if="user.is_admin">
            <h1>Create Product</h1>

            <transition name="fade">
                <div class="alert alert-danger alert-dismissible fade show" role="alert" v-if="error">
                    <b>{{ error.message }}</b>
                    <ul>
                        <li v-for="(errorName, index) in error.errors" :key="index">
                            {{ errorName[0] }}
                        </li>
                    </ul>
                    <button type="button" class="btn-close" @click="error = null"></button>
                </div>
            </transition>

            <div class="form-group">
                <label>Name</label>
                <input v-model="product.name" type="text" class="form-control" placeholder="Name..." />
            </div>

            <div class="form-group">
                <label>Price</label>
                <input v-model="product.price" type="text" class="form-control" placeholder="Price..." />
            </div>

            <button class="btn btn-primary mt-2" @click="createProduct">Create</button>

            <hr>
        </template>

        <h1>List Products</h1>

        <div v-if="loading" class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>

        <div class="search-input" v-if="!loading">
            <input type="text" v-model="keyword">
            <button @click="handleSearch">Search</button>
        </div>

        <table class="table" v-if="!loading">
            <thead>
                <tr>
                    <th scope="col">ID</th>
                    <th scope="col">Name</th>
                    <th scope="col">Price</th>
                    <th scope="col" v-if="user.is_admin">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(product, index) in listProducts.data" :key="product.id">
                    <th scope="row">{{ product.id }}</th>

                    <td v-if="!product.isEdit">{{ product.name }}</td>
                    <td v-else>
                        <input type="text" v-model="selectedProduct.name" class="form-control">
                    </td>

                    <td v-if="!product.isEdit">{{ product.price }}</td>
                    <td v-else>
                        <input type="text" v-model="selectedProduct.price" class="form-control">
                    </td>

                    <template v-if="user.is_admin">
                        <td v-if="!product.isEdit">
                        <button class="btn btn-primary" @click="selectProduct(product, index)">Edit</button>
                        <button class="btn btn-danger ms-2" @click="deleteProduct(product, index)">Delete</button>
                        </td>
                        <td v-else>
                            <button class="btn btn-primary" @click="updateProduct(index)">Save</button>
                            <button class="btn btn-danger ms-2" @click="product.isEdit = false">Cancel</button>
                        </td>
                    </template>
                </tr>
            </tbody>
        </table>

        <!-- pagination -->
        <ul class="pagination" v-if="!loading">
            <li class="page-item" :class="{ 'disabled': listProducts.prev_page_url === null }"
                @click="listProducts.prev_page_url && getProducts(listProducts.current_page - 1, keyword)">
                <a class="page-link" href="#">Previous</a>
            </li>

            <li class="page-item" :class="{ 'active' : page === listProducts.current_page }" v-for="page in totalPages" :key="page" @click="getProducts(page, keyword)">
                <a class="page-link" href="#">{{ page }}</a>
            </li>

            <li class="page-item" :class="{ 'disabled': listProducts.next_page_url === null }"
                @click="listProducts.next_page_url && getProducts(listProducts.current_page + 1, keyword)">
                <a class="page-link" href="#">Next</a>
            </li>
        </ul>

    </div>
</template>

<script setup>
import { toast } from 'vue3-toastify';
import 'vue3-toastify/dist/index.css';
import axios from "axios";
import { onBeforeMount, ref } from "vue";

const product = ref({
    name: '',
    price: 0
});

const listProducts = ref({});
const error = ref(null);
const selectedProduct = ref(null);
const loading = ref(true);
const user = ref(window.__user__);
const keyword = ref('');
const totalPages = ref(null);

onBeforeMount(() => {
    getProducts();
});

const createProduct = async () => {
    try {
        error.value = null
        const response = await axios.post('/products', {
            name: product.value.name,
            price: product.value.price
        });

        // insert element in fisrt position array
        listProducts.value.data.unshift({
            ...response.data.product,
            isEdit: false
        })

        // reset input form
        product.value = {
            name: '',
            price: 0
        }

        // toastify
        toast("Thêm mới thành công sản phẩm " + response.data.product.name, {
            type: "success",
            autoClose: 1000,
        });

    } catch (e) {
        console.log(e.response);

        error.value = e.response.data;
    }
}

const getProducts = async (page = 1, keyword = "") => {
    try {
        const response = await axios.get(`/products?page=${page}&keyword=${keyword}`);
        listProducts.value = response.data;

        listProducts.value.data.forEach(item => {
            item.isEdit = false
        })

        totalPages.value = response.data.last_page;
    } catch (e) {
        console.log(e);

        error.value = e.response.data
    } finally {
        loading.value = false;
    }
}

const selectProduct = (product, index) => {
    listProducts.value.data.forEach(item => {
        item.isEdit = false
    })

    product.isEdit = true
    selectedProduct.value = { ...product }
}

const updateProduct = async (index) => {
    try {
        // call API update
        const response = await axios.put('/products/' + selectedProduct.value.id, {
            name: selectedProduct.value.name,
            price: selectedProduct.value.price
        });

        // update UI
        listProducts.value.data[index].name = response.data.product.name
        listProducts.value.data[index].price = response.data.product.price
        listProducts.value.data[index].isEdit = false;

        // toastify
        toast("Chỉnh sửa thành công sản phẩm " + response.data.product.name, {
            type: "success",
            autoClose: 1000,
        });

    } catch (e) {
        console.log(e.response.data);
        error.value = e.response.data
    }
}

const deleteProduct = async (product, index) => {

    if (!confirm("Bạn chắc xóa sản phẩm " + product.name + "?")) {
        return;
    }

    try {
        await axios.delete('/products/' + product.id)
        // listProducts.value.data.splice(index, 1);

        getProducts();
    } catch (e) {
        error.value = e.response.data

        if(e.response.status === 403) {
            toast("Forbidden", {
                type: "error",
                autoClose: 1000,
            });
        } else {
            toast("Error server", {
                type: "error",
                autoClose: 1000,
            });
        }


    }
}

const handleSearch = () => {
    getProducts(1, keyword.value);
    console.log(keyword.value);
}

</script>

<style lang="scss" scoped>
fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
