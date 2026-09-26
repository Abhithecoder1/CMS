/**
 * Cloudinary Media Gallery Configuration
 *
 * Keep Cloudinary configuration separate from UI logic.
 * Do NOT expose API secrets or private credentials here.
 */

const CLOUDINARY_CONFIG = {
  // Public Cloud Name (Default sample demo cloud name)
  cloudName: 'demo',

  // Default sample media items hosted on Cloudinary
  mediaItems: [
    {
      url: 'https://res.cloudinary.com/demo/image/upload/cld-sample.jpg',
      title: 'Mountain Landscape',
      type: 'image',
      format: 'jpg'
    },
    {
      url: 'https://res.cloudinary.com/demo/video/upload/sea_turtle.mp4',
      poster: 'https://res.cloudinary.com/demo/video/upload/so_0/sea_turtle.jpg',
      title: 'Sea Turtle Swimming',
      type: 'video',
      format: 'mp4'
    },
    {
      url: 'https://res.cloudinary.com/demo/image/upload/cld-sample-2.jpg',
      title: 'Delicious Food Presentation',
      type: 'image',
      format: 'jpg'
    },
    {
      url: 'https://res.cloudinary.com/demo/video/upload/elephants.mp4',
      poster: 'https://res.cloudinary.com/demo/video/upload/so_0/elephants.jpg',
      title: 'Elephants in Savannah',
      type: 'video',
      format: 'mp4'
    },
    {
      url: 'https://res.cloudinary.com/demo/image/upload/sample.webp',
      title: 'Sample WebP Image',
      type: 'image',
      format: 'webp'
    },
    {
      url: 'https://res.cloudinary.com/demo/image/upload/sample.gif',
      title: 'Sample Animated Graphic',
      type: 'image',
      format: 'gif'
    },
    {
      url: 'https://res.cloudinary.com/demo/video/upload/dog.mp4',
      poster: 'https://res.cloudinary.com/demo/video/upload/so_0/dog.jpg',
      title: 'Playful Dog',
      type: 'video',
      format: 'mp4'
    },
    {
      url: 'https://res.cloudinary.com/demo/image/upload/cld-sample-3.jpg',
      title: 'Basketball Player',
      type: 'image',
      format: 'jpg'
    }
  ]
};
