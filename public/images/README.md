# Product Images Directory

## Default Image
Place your `MeatDay_shop_image.jpg` file in this directory.

The default image will be used when:
- A product has no image uploaded
- The uploaded image file is missing or corrupted
- The product_image field is null or empty

## Image Specifications
- **File name**: `MeatDay_shop_image.jpg`
- **Recommended size**: 800x580 pixels (to match product image requirements)
- **Format**: JPG/JPEG
- **File size**: Under 2MB

## Usage
The default image will be automatically served at:
`https://yourdomain.com/images/MeatDay_shop_image.jpg`

## Product Image Storage
Uploaded product images are stored in:
`storage/app/public/product_images/`

And are accessible via:
`https://yourdomain.com/storage/product_images/filename.jpg` 