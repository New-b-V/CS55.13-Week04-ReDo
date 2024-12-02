import Head from 'next/head';
import Layout from '../components/layout';
import { getAllIds, getData } from '../lib/data';


export async function getStaticProps( { params } ) {
  const itemData = await getData(params.id);
  return {
    props: {
      itemData
    }
  };
}


export async function getStaticPaths() {
  const paths = await getAllIds();
  return {
    paths,
    fallback: false
  };
}


export default function Entry( { itemData } ) {
  return (
    <Layout>
      <article className="card col-6">
        <div className="card-body">
          <h4 className="card-title">{itemData.post_title}</h4>
          <h5 className="card-subtitle mb-2 text-muted">{itemData.user_login}</h5>
          <div className="card-text" dangerouslySetInnerHTML={{__html: itemData.post_content}}/>
          
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