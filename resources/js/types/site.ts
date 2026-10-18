/**
 * Public-site (جمعية رانيا) domain types.
 * These mirror the Inertia resources in app/Http/Resources.
 */

export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    courses_count?: number;
}

export interface Lesson {
    id: number;
    title: string;
    content: string | null;
    video_url: string | null;
    attachment: string | null;
    attachment_url: string | null;
    duration_minutes: number | null;
    position: number;
}

export interface Instructor {
    id: number;
    name: string;
    specialization: string | null;
    bio: string | null;
    avatar: string | null;
    initials: string;
    courses_count?: number;
    courses?: Course[];
}

export interface CourseLevel {
    value: string | null;
    label: string | null;
}

export interface Course {
    id: number;
    title: string;
    slug: string;
    summary: string | null;
    description: string | null;
    cover_image: string | null;
    level: CourseLevel;
    price: number;
    is_free: boolean;
    duration_hours: number | null;
    capacity: number | null;
    is_featured: boolean;
    starts_at: string | null;
    ends_at: string | null;
    lessons_count?: number;
    students_count?: number;
    enrollments_count?: number;
    category?: Category;
    instructor?: Instructor;
    lessons?: Lesson[];
    url: string;
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Paginated<T> {
    data: T[];
    links: {
        first: string | null;
        last: string | null;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number | null;
        to: number | null;
        links: PaginationLink[];
    };
}

export interface EnrollmentStatusValue {
    value: string;
    label: string;
}

export interface Enrollment {
    id: number;
    status: EnrollmentStatusValue;
    progress: number;
    enrolled_at: string | null;
    completed_at: string | null;
    created_at: string | null;
    course?: Course;
}

export interface EnrollmentStats {
    total: number;
    pending: number;
    active: number;
    completed: number;
}
