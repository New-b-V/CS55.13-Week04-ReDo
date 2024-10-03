import Head from 'next/head';
import Link from 'next/link';

export default function Layout( { children, home } ) {
  return (
    <div>
      <Head>
        <title>Basic Next.js App</title>
      </Head>
      <header>
        <nav>
        <a href="https://www.liveeatlearn.com/list-of-fruits/">Types of Fruit</a>
        </nav>
      </header>
      <main>
        {children}
      </main>
      {!home && (
          <Link href="/" className="btn btn-primary mt-3">
            ← Back to home
          </Link>
        )
      }
      <footer>
        <p>Copyright 2024 VJ</p>
      </footer>
    </div>
  );
}