import Layout from '../components/layout';
import { getAllIds, getData } from '../lib/data';

// define a getStaticProps() function to have next.js retrieve data to use for the dynamic page
// - this name is defined by next.js
export async function getStaticProps( { params } ) {
  const itemData = await getData(params.id);
  return {
    props: {
      itemData
    }
  };
}

// define a getStaticPaths() function to tell next.js all valid URLs: 1,2,3,4 
// - this name is defined by next.js
export async function getStaticPaths() {
  const paths = await getAllIds();
  return {
    paths,
    fallback: false
  };
}

// export our dynamically routed page component Entry
export default function Entry( { itemData } ) {
  return (
    <Layout>
      <article className="card col-6">
        <div className="card-body">
          <h4 className="card-title">{itemData.name}</h4>
          <h5 className="card-subtitle mb-2 text-body-secondary">{itemData.phone}</h5>
          <p className="card-text">{itemData.birthdate}</p>
          <a href="#" className="card-link">{itemData.email}</a>
          <h6>Characteristics</h6>
          <ol>
            {itemData.characteristic && itemData.characteristic.map(
                ({id, characteristic}) => (
                  <li key={id}>
                    {characteristic}
                  </li>
                )
              )
            }
          </ol>
        </div>
      </article>
    </Layout>
  );
}