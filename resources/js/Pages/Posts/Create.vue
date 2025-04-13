<script setup lang="ts">
import { onBeforeUnmount, ref } from "vue";
import Button from "@/Components/ui/button/Button.vue";
import Card from "@/Components/ui/card/Card.vue";
import CardContent from "@/Components/ui/card/CardContent.vue";
import CardDescription from "@/Components/ui/card/CardDescription.vue";
import CardHeader from "@/Components/ui/card/CardHeader.vue";
import CardTitle from "@/Components/ui/card/CardTitle.vue";

import Label from "@/components/ui/label/Label.vue";
import MainLayout from "@/Layouts/MainLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { toast } from "vue-sonner";
import { XCircle } from "lucide-vue-next";
import TiptapEditor from "@/Components/tiptap/TiptapEditor.vue";
import Alert from "@/Components/ui/alert/Alert.vue";
import AlertTitle from "@/Components/ui/alert/AlertTitle.vue";
import AlertDescription from "@/Components/ui/alert/AlertDescription.vue";
import CardFooter from "@/Components/ui/card/CardFooter.vue";
import { Input } from "@/Components/ui/input";

// --- Types ---
interface IPostFormData {
    title: string;
    content: string; // Will store HTML from Tiptap
    image: File | null;
    [key: string]: string | File | null; // Ensure compatibility with FormDataConvertible
}

// --- Refs & State ---
const imageInputRef = ref<HTMLInputElement | null>(null);
const imagePreviewUrl = ref<string | null>(null);

const MAX_SIZE_MB = 2;
const ALLOWED_TYPES = ["image/jpeg", "image/png", "image/gif"];

// --- Inertia Form ---

const inertiaForm = useForm<IPostFormData>({
    title: "",
    content: "", // Initialize empty, Tiptap will update via v-model
    image: null,
});

// --- Image Handling ---
// Function to trigger file input click
const triggerImageInput = () => {
    if (imageInputRef.value) {
        imageInputRef.value.click();
    } else {
        console.error("imageInputRef is not properly initialized!");
    }
};

const handleImageChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0] || null;

    if (!file) {
        removeImage(); // Clear if selection was cancelled
        return;
    }
    // Validate file type
    if (!ALLOWED_TYPES.includes(file.type)) {
        toast.error("Invalid image type.", {
            description: "Only JPG, PNG, and GIF files are allowed.",
        });
        return;
    }

    if (!file.type.startsWith("image/")) {
        toast.error("Please select a valid image file.", {
            description: "Only image files are allowed.",
        });
        return;
    }

    // Validate file size
    if (file.size > MAX_SIZE_MB * 1024 * 1024) {
        toast.error(`File size exceeds ${MAX_SIZE_MB}MB.`, {
            description: "Please select a smaller image.",
        });
        return;
    }

    inertiaForm.image = file;

    if (imagePreviewUrl.value) {
        URL.revokeObjectURL(imagePreviewUrl.value); // Revoke old URL if it exists
    }

    imagePreviewUrl.value = URL.createObjectURL(file); // Create new URL for preview
};

const removeImage = () => {
    inertiaForm.image = null;
    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value);
    imagePreviewUrl.value = null;
    if (imageInputRef.value) imageInputRef.value.value = ""; // Reset input
};

// --- Form Submission ---
// --- Form Submission ---
const submitPost = () => {
    // Content is already synced via v-model on TiptapEditor
    if (!inertiaForm.content.trim() || inertiaForm.content === "<p></p>") {
        // Basic check for empty Tiptap content
        inertiaForm.setError("content", "Post content cannot be empty.");
        toast({
            variant: "destructive",
            title: "Validation Error",
            description: "Post content cannot be empty.",
        });
        return;
    }

    inertiaForm.post(route("posts.store"), {
        onSuccess: () => {
            toast({
                title: "Success!",
                description: "Post created successfully.",
            });
            inertiaForm.reset(); // Resets title, content, image
            removeImage(); // Explicitly clear preview/input just in case reset doesn't fully clear File object state
            // The TiptapEditor's watch effect will clear its content when inertiaForm.content resets
        },
        onError: (errors) => {
            console.error("Post creation error:", errors);
            toast({
                variant: "destructive",
                title: "Submission Error",
                description: "Please check the form for errors.",
            });
        },
    });
};

// --- Lifecycle ---
onBeforeUnmount(() => {
    // Clean up object URL
    if (imagePreviewUrl.value) URL.revokeObjectURL(imagePreviewUrl.value);
});
</script>

<template>
    <MainLayout>
        <Head title="Create New Post" />

        <div class="container mx-auto px-4 py-8 md:py-12 max-w-4xl">
            <Card>
                <CardHeader>
                    <CardTitle class="text-2xl font-bold"
                        >Create New Post</CardTitle
                    >
                    <CardDescription
                        >Share your knowledge and thoughts with the
                        community.</CardDescription
                    >
                </CardHeader>

                <form @submit.prevent="submitPost">
                    <CardContent class="space-y-6">
                        <!-- Image Upload Section -->
                        <div class="space-y-2">
                            <Label for="post-image-button"
                                >Cover Image (Optional)</Label
                            >

                            <input
                                type="file"
                                ref="imageInputRef"
                                class="hidden"
                                @change="handleImageChange"
                            />
                            <!-- <Input
                                type="file"
                                accept="image/*"
                                class="hidden"
                                ref="imageInputRef"
                                @change="handleImageChange"
                            /> -->
                            <Button
                                type="button"
                                variant="outline"
                                @click="triggerImageInput"
                                id="post-image-button"
                                class="w-full sm:w-auto"
                            >
                                {{
                                    inertiaForm.image
                                        ? "Change Image"
                                        : "Upload Image"
                                }}
                            </Button>
                            <div
                                v-if="imagePreviewUrl"
                                class="mt-4 relative border rounded-md overflow-hidden"
                            >
                                <img
                                    :src="imagePreviewUrl"
                                    alt="Image Preview"
                                    class="max-h-60 w-auto object-contain mx-auto"
                                />
                                <Button
                                    type="button"
                                    variant="destructive"
                                    size="icon"
                                    @click="removeImage"
                                    class="absolute top-2 right-2 h-7 w-7 rounded-full opacity-80 hover:opacity-100"
                                    aria-label="Remove image"
                                >
                                    <XCircle class="h-4 w-4" />
                                </Button>
                            </div>
                            <p
                                v-if="inertiaForm.errors.image"
                                class="text-sm font-medium text-destructive"
                            >
                                {{ inertiaForm.errors.image }}
                            </p>
                        </div>

                        <!-- Title Input -->
                        <div class="space-y-2">
                            <Label for="post-title"
                                >Post Title
                                <span class="text-red-500">*</span></Label
                            >
                            <Input
                                id="post-title"
                                type="text"
                                placeholder="Your Awesome Post Title"
                                v-model="inertiaForm.title"
                                :aria-invalid="!!inertiaForm.errors.title"
                                aria-describedby="title-error"
                                required
                            />
                            <p
                                v-if="inertiaForm.errors.title"
                                id="title-error"
                                class="text-sm font-medium text-destructive"
                            >
                                {{ inertiaForm.errors.title }}
                            </p>
                        </div>

                        <!-- Tiptap Editor for Content -->
                        <div class="space-y-2">
                            <Label for="post-content"
                                >Content
                                <span class="text-red-500">*</span></Label
                            >
                            <!-- Use the TiptapEditor component -->
                            <!-- v-model syncs inertiaForm.content with the editor's output -->
                            <TiptapEditor
                                id="post-content"
                                v-model="inertiaForm.content"
                                :has-error="!!inertiaForm.errors.content"
                                aria-describedby="content-error"
                                placeholder="Write your post here using markdown or the toolbar..."
                            />
                            <p
                                v-if="inertiaForm.errors.content"
                                id="content-error"
                                class="text-sm font-medium text-destructive"
                            >
                                {{ inertiaForm.errors.content }}
                            </p>
                        </div>

                        <!-- General Form Errors -->
                        <Alert
                            v-if="
                                inertiaForm.hasErrors &&
                                !inertiaForm.errors.title &&
                                !inertiaForm.errors.content &&
                                !inertiaForm.errors.image
                            "
                            variant="destructive"
                        >
                            <AlertTitle>Error</AlertTitle>
                            <AlertDescription>{{
                                Object.values(inertiaForm.errors)[0] ??
                                "An unexpected error occurred."
                            }}</AlertDescription>
                        </Alert>
                    </CardContent>

                    <CardFooter class="flex justify-end pt-6">
                        <Button
                            type="submit"
                            :disabled="inertiaForm.processing"
                        >
                            <span v-if="inertiaForm.processing"
                                >Publishing...</span
                            >
                            <span v-else>Publish Post</span>
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </MainLayout>
</template>
